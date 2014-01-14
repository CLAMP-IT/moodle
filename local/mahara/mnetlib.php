<?php

require_once dirname(__FILE__) . '/optionalib.php';

/**
 * Component for handling common related mahara requests
 */
class mahara_mnetservice {
  const PORTFOLIO_TABLE = 'mahara_portfolio';

  private $hostid;

  /**
   * Constructor
   *
   * @param int $hostid
   */
  public function __construct($hostid = -1) {
    $this->hostid = $hostid;
  }

  /**
   * Gets the host id
   *
   * @return int
   */
  public function get_host() {
    return $this->hostid;
  }

  /**
   * Sets the host id for this service
   *
   * @param int $hostid
   * @return mahara_mnetservice
   */
  public function set_host($hostid) {
    $this->hostid = $hostid;
    return $this;
  }

  /**
   * Gets the jump URL
   *
   * @param string $wantsurl
   * @return moodle_url
   */
  public function get_jumpurl($wantsurl) {
    return new moodle_url('/auth/mnet/jump.php', array(
      'hostid' => $this->hostid,
      'wantsurl' => $wantsurl,
    ));
  }

  /**
   * Gets the Mahara pages for user submission
   *
   * @param int $userid
   * @param string $query (Optional)
   * @return Model_Either (array errors, array pages)
   */
  public function request_pages_for_user($userid, $query = null) {
    global $DB;

    $user = $DB->get_record('user', array('id' => $userid));
    return $this
      ->call_mnet_peer(
        function($client) use ($user, $query) {
          $client->set_method('mod/mahara/rpclib.php/get_views_for_user');
          $client->add_param($user->username);
          $client->add_param($query);
        })
      ->withRight()
      ->map(
        function($pages) use ($userid) {
          $event = new stdClass;
          $event->pages = $pages;
          $event->userid = $userid;

          events_trigger('mahara_get_pages_for_user', $event);
          return $event->pages;
        });
  }

  /**
   * Submits a Mahara page for grading
   *
   * @param int $userid
   * @param int $viewid
   * @return Model_Either (array errors, stdClass portfolio)
   */
  public function request_submit_page_for_user($userid, $viewid) {
    global $DB;

    $user = $DB->get_record('user', array('id' => $userid));
    $service = $this;
    return $this
      ->call_mnet_peer(
        function($client) use ($user, $viewid) {
          $client->set_method('mod/mahara/rpclib.php/submit_view_for_assessment');
          $client->add_param($user->username);
          $client->add_param($viewid);
        })
      ->withRight()
      ->map(
        function($response) use ($service, $userid) {
          $page = (object) $response;

          $event = new stdClass;
          $event->page = $page;
          $event->userid = $userid;
          $event->host = $service->get_host();
          events_trigger('mahara_submit_view_for_assessment', $event);

          return $service->add_update_portfolio($userid, $event->page);
        });
  }

  /**
   * Requests the releasing of a submitted view
   *
   * @param int $userid
   * @param int $viewid
   * @param array $meta (Optional)
   */
  public function request_release_submitted_view($userid, $viewid, $meta = array()) {
    global $DB;

    $service = $this;
    $user = null;
    if ($userid) {
      $user = $DB->get_record('user', array('id' => $userid));
    }
    return $this
      ->call_mnet_peer(
        function($client) use ($user, $viewid, $meta) {
          $client->set_method('mod/mahara/rpclib.php/release_submitted_view');
          $client->add_param($viewid);
          $client->add_param($meta);
          $client->add_param(isset($user) ? $user->username : null);
        })
      ->withRight()
      ->each(
        function() use ($service, $userid, $viewid) {
          $event = new stdClass;
          $event->userid = $userid;
          $event->viewid = $viewid;
          $event->host = $service->get_host();

          events_trigger('mahara_release_submitted_view', $event);
        });
  }

  /**
   * Adds or updates a portfolio by its Mahara data
   *
   * @param stdClass $userid
   * @param stdClass $page
   * @return stdClass
   */
  public function add_update_portfolio($userid, $page) {
    global $DB;

    $service = $this;
    $table = self::PORTFOLIO_TABLE;
    return $this
      ->get_portfolio($page->id)
      ->orElse(function() use ($userid, $page) {
        $portfolio = new stdClass;
        $portfolio->userid = $userid;
        $portfolio->page = $page->id;
        return $portfolio;
      })
      ->map(function($portfolio) use ($DB, $service, $page, $table) {
        $portfolio->title = $page->title;
        $portfolio->url = $page->url;
        $portfolio->host = $service->get_host();

        if (empty($portfolio->id)) {
          $portfolio->id = $DB->insert_record($table, $portfolio);
          $eventname = 'mahara_portfolio_added';
        } else {
          $DB->update_record($table, $portfolio);
          $eventname = 'mahara_portfolio_updated';
        }

        events_trigger($eventname, $portfolio);

        return $portfolio;
      })
      ->get();
  }

  /**
   * Gets a single portfolio by its Moodle id
   *
   * @param int $portfolioid
   * @return Model_Option
   */
  public function get_local_portfolio($portfolioid) {
    global $DB;
    $obj = $DB->get_record(self::PORTFOLIO_TABLE, array(
      'id' => $portfolioid,
    ));

    return $obj ? new Model_Some($obj) : new Model_None();
  }

  /**
   * Gets a single portfolio by its Mahara id
   *
   * @param int $viewid
   * @return Model_Option
   */
  public function get_portfolio($viewid) {
    global $DB;
    $obj = $DB->get_record(self::PORTFOLIO_TABLE, array(
      'page' => $viewid,
      'host' => $this->hostid,
    ));

    return $obj ? new Model_Some($obj) : new Model_None();
  }

  /**
   * Gets a list of portfolios associated with a user
   *
   * @param int $userid
   * @return array
   */
  public function get_users_portfolios($userid) {
    global $DB;
    return $DB->get_records(self::PORTFOLIO_TABLE, array(
      'userid' => $userid
    ));
  }

  /**
   * Makes a request to the mnet peer
   *
   * @param callable $callback
   * @return Model_Either
   */
  private function call_mnet_peer($callback) {
    global $CFG;

    require_once "{$CFG->dirroot}/mnet/xmlrpc/client.php";
    require_once "{$CFG->dirroot}/mnet/peer.php";

    $server = new mnet_peer();
    $server->set_id($this->hostid);
    $client = new mnet_xmlrpc_client();

    if (is_callable($callback)) {
      $callback($client);
    }

    return $client->send($server) === true ?
      new Model_Right($client->response) :
      new Model_Left($client->error);
  }

  /**
   * Removes the portfolio record from the DB
   *
   * @param stdClass $portfolio
   * @return boolean
   */
  public function delete_portfolio($portfolio) {
    global $DB;

    events_trigger('mahara_portfolio_deleted', $portfolio);
    return $DB->delete_records(self::PORTFOLIO_TABLE, array('id' => $portfolio->id));
  }

  /**
   * Does nothing
   */
  public function donothing() {
  }
}
