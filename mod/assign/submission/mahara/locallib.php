<?php

require_once "{$CFG->dirroot}/local/mahara/mnetlib.php";

class assign_submission_mahara extends assign_submission_plugin {
  const VIEWS_TABLE = 'assign_mahara_submit_views';

  const STATUS_SELECTED = 'selected';
  const STATUS_SUBMITTED = 'submitted';
  const STATUS_RELEASED = 'released';

  private $service;

  /**
   * Gets the Mahara mnet service
   *
   * @return mahara_mnetservice
   */
  public function get_service() {
    if (is_null($this->service)) {
      $hostid = $this->get_config('mahara_host');
      $this->service = new mahara_mnetservice($hostid);
    }

    return $this->service;
  }

  /**
   * Returns the DB record for the mnet host
   *
   * @return stdClass
   */
  public function get_remotehost() {
    global $DB;
    return $DB->get_record('mnet_host', array(
      'id' => $this->get_service()->get_host(),
    ));
  }

  /**
   * Gets the Mahara hosts doing SSO with
   *
   * @return array
   */
  private function get_hosts() {
    global $DB;

    $sql = "
         SELECT DISTINCT
             h.id,
             h.name
         FROM
             {mnet_host} h,
             {mnet_application} a,
             {mnet_host2service} h2s_IDP,
             {mnet_service} s_IDP,
             {mnet_host2service} h2s_SP,
             {mnet_service} s_SP
         WHERE
             h.id != :mnet_localhost_id AND
             h.id = h2s_IDP.hostid AND
             h.deleted = 0 AND
             h.applicationid = a.id AND
             h2s_IDP.serviceid = s_IDP.id AND
             s_IDP.name = 'sso_idp' AND
             h2s_IDP.publish = '1' AND
             h.id = h2s_SP.hostid AND
             h2s_SP.serviceid = s_SP.id AND
             s_SP.name = 'sso_idp' AND
             h2s_SP.publish = '1' AND
             a.name = 'mahara'
         ORDER BY
             h.name";

    $hostId = get_config('moodle', 'mnet_localhost_id');
    return $DB->get_records_sql_menu($sql, array(
      'mnet_localhost_id' => $hostId,
    ));
  }

  /**
   * @see parent
   *
   * @return bool
   */
  public function delete_instance() {
    global $DB;

    $params = array(
      'assignment' => $this->assignment->get_instance()->id,
    );

    $this->release_all_submitted();
    events_trigger('assignsubmission_mahara_delete_instance', (object) $params);
    return ($DB->delete_records(self::VIEWS_TABLE, $params));
  }

  /**
   * Retrieves all of the submitted postfolio submissions
   *
   * @param array $submissionids
   * @return array
   */
  public function get_all_submitted(array $submissionids = null) {
    global $DB;

    $params = array(
      'assignment' => $this->assignment->get_instance()->id,
      'status' => self::STATUS_SUBMITTED,
    );

    if ($submissionids) {
      $select = 'submission IN (' . implode(',', $submissionids) . ') '
        . 'AND assignment = :assignment AND status = :status';
      return $DB->get_records_select(self::VIEWS_TABLE, $select, $params);
    } else {
      return $DB->get_records(self::VIEWS_TABLE, $params);
    }
  }

  /**
   * Gets all submissions that are drafts and re-opens
   *
   * @return array
   */
  public function get_all_drafts_or_reopens() {
    global $DB;

    $select = 'assignment = :assignment AND (status = :status1 OR status = :status2)';
    return $DB->get_records_select('assign_submission', $select, array(
      'assignment' => $this->assignment->get_instance()->id,
      'status1' => ASSIGN_SUBMISSION_STATUS_REOPENED,
      'status2' => ASSIGN_SUBMISSION_STATUS_DRAFT,
    ));
  }

  /**
   * Releases all of the portfolios that have been submitted
   */
  public function release_all_submitted() {
    foreach ($this->get_all_submitted() as $submission) {
      $this->release_submission($submission);
    }
  }

  /**
   * Gets a single submitted portfolio status record
   *
   * @param stdClass $submission
   * @return stdClass
   */
  public function get_portfolio_record($submission) {
    global $DB;

    return $DB->get_record(self::VIEWS_TABLE, array(
      'submission' => $submission->id,
      'assignment' => $this->assignment->get_instance()->id,
    ));
  }

  /**
   * Adds or updates a portfolio submitted status record
   *
   * @param stdClass $submission
   * @param stdClass $portfolio
   * @param string $status (Optional)
   * @return boolean
   */
  public function add_update_portfolio_record($submission, $portfolio, $status = self::STATUS_SELECTED) {
    global $DB;

    $status_record = $this->get_portfolio_record($submission);
    if (empty($status_record)) {
      $status_record = new stdClass;
      $status_record->submission = $submission->id;
      $status_record->assignment = $this->assignment->get_instance()->id;
    }

    $status_record->portfolio = $portfolio->id;
    $status_record->status = $status;

    if (empty($status_record->id)) {
      $status_record->id = $DB->insert_record(self::VIEWS_TABLE, $status_record);
      $success = $status_record->id > 0;
    } else {
      $success = $DB->update_record(self::VIEWS_TABLE, $status_record);
    }

    events_trigger('assignsubmission_mahara_add_update_submission', $status_record);
    return $success;
  }

  /**
   * @see parent
   * @return string
   */
  public function get_name() {
    return get_string('pluginfile', 'assignsubmission_mahara');
  }

  /**
   * Gets the preview (popup) and link out for the portfolio
   *
   * @param string $name
   * @param string|moodle_url $url
   * @param string $title (Optional)
   * @return string
   */
  public function get_preview_url($name, $url, $title = null) {
    global $OUTPUT;

    $icon = $OUTPUT->pix_icon('t/preview', $name);
    $params = array('target' => '_blank', 'title' => $title ?: $name);

    $popup_icon = html_writer::link($url, $icon, $params + array(
      'class' => 'portfolio popup',
    ));

    $link = html_writer::link($url, $name, $params);

    return "$popup_icon $link";
  }

  /**
   * @see parent
   *
   * @param sdtClass $submission
   * @return string
   */
  public function view(stdClass $submission) {
    global $PAGE, $OUTPUT;

    $PAGE->requires->js('/mod/assign/submission/mahara/js/popup.js');

    if ($submitted = $this->get_portfolio_record($submission)) {
      $plugin = $this;
      return $plugin
        ->get_service()
        ->get_local_portfolio($submitted->portfolio)
        ->map(function($portfolio) use ($plugin) {
          $viewurl = $plugin->get_service()->get_jumpurl($portfolio->url);
          return $plugin->get_preview_url($portfolio->title, $viewurl);
        })
        ->getOrElse('');
    }

    return '';
  }

  /**
   * @see parent
   *
   * @param stdClass $submission
   * @param bool $showviewlink (Mutable)
   * @return string
   */
  public function view_summary(stdClass $submission, &$showviewlink) {
    return $this->view($submission);
  }

  /**
   * @see parent
   *
   * @param string $action
   * @return string
   */
  public function view_page($action) {
      return '';
  }

  /**
   * @see parent
   *
   * @param MoodleQuickForm $form
   */
  public function get_settings(MoodleQuickForm $form) {
    $hostId =
      $this->get_config('mahara_host') ?:
      get_config('assignsubmission_mahara', 'host');

    $hosts = $this->get_hosts();

    if ($hosts) {
      $form->addElement('select', 'mahara_host', get_string('site', 'assignsubmission_mahara'), $hosts);
      $form->addHelpButton('mahara_host', 'site', 'assignsubmission_mahara');
      $form->setDefault('mahara_host', isset($hosts[$hostId]) ? $hosts[$hostId] : key($hosts));

      if ($form->getElementType('assignsubmission_mahara_enabled') == 'selectyesno') {
        $form->disabledIf('mahara_host', 'assignsubmission_mahara_enabled', 'eq', '0');
      } else {
        $form->disabledIf('mahara_host', 'assignsubmission_mahara_enabled');
      }

      $event = new stdClass;
      $event->form = $form;
      $event->plugin = $this;
      events_trigger('assignsubmission_mahara_get_settings', $event);
    } else {
      $pluginname = get_string('pluginname', 'assignsubmission_mahara');
      $no_host = get_string('nomaharahostsfound', 'assignsubmission_mahara');

      $form->addElement('static', 'mahara_warning', $pluginname, "<span class='error'>$no_host</span>");
      if ($form->elementExists('assignsubmission_mahara_enabled')) {
        $form->removeElement('assignsubmission_mahara_enabled');
      }
    }
  }

  /**
   * @see parent
   *
   * @param stdClass $formdata
   * @return bool
   */
  public function save_settings(stdClass $formdata) {
    $hosts = $this->get_hosts();
    if (!isset($hosts[$formdata->mahara_host])) {
      $this->set_error(get_string('err_selected_host', 'assignsubmission_mahara'));
      return false;
    }

    $this->set_config('mahara_host', $formdata->mahara_host);

    events_trigger('assignsubmission_mahara_save_settings', $formdata);

    return true;
  }

  /**
   * @see parent
   *
   * @param $submission
   * @param MoodleQuickForm $form
   * @param stdClass $data
   * @param int $userid
   * @return bool
   */
  public function get_form_elements_for_user($submission, MoodleQuickForm $form, stdClass $data, $userid) {
    global $PAGE;

    $PAGE->requires->js('/mod/assign/submission/mahara/js/popup.js');
    $PAGE->requires->js('/mod/assign/submission/mahara/js/filter.js');

    $remote_capable = has_capability(
      'moodle/site:mnetlogintoremote',
      $this->assignment->get_course_context()
    );

    if (!is_enabled_auth('mnet')) {
      print_error('authmnetdisabled', 'mnet');
    } else if (!$remote_capable) {
      print_error('notpermittedtojump', 'mnet');
    }

    $plugin = $this;
    return $this
      ->get_service()
      ->request_pages_for_user($userid)
      ->fold(
        function($errors) use ($plugin) {
          $plugin->on_error_handler($errors);
          print_error('err_mnetclient', 'assignsubmission_mahara', '', $plugin->get_error());
        },

        function($data) use ($submission, $form, $plugin) {
          $plugin->format_pages($submission, $form, (object)$data);
          return true;
        });
  }

  /**
   * Format Mahara pages for the user
   *
   * @param stdClass $submission
   * @param MoodleQuickForm $form
   * @param stdClass $response
   */
  public function format_pages($submission, $form, stdClass $response) {
    global $OUTPUT;

    $form->addElement('text', 'search', get_string('search'));
    $form->setType('search', PARAM_RAW);
    $form->addElement('html', '<hr/><br/>');

    $host = $this->get_remotehost();
    if (empty($response->data)) {
      $form->addElement('static', 'no_pages',
        "<strong>" . get_string('error') . "</strong>",
        get_string('noviewsfound', 'assignsubmission_mahara', $host->name)
      );
      return;
    }

    $form->addElement('static', 'view_by',
      "<strong>$host->name</strong>:",
      get_string('viewsby', 'assignsubmission_mahara', $response->displayname)
    );

    foreach ($response->data as $view) {
      $viewurl = $this->get_service()->get_jumpurl($view['url']);

      $anchor = $this->get_preview_url($view['title'], $viewurl, strip_tags($view['description']));

      $form->addElement('radio', 'view', '', $anchor, $view['id']);
    }

    if ($submission && $submitted = $this->get_portfolio_record($submission)) {
      $this
        ->get_service()
        ->get_local_portfolio($submitted->portfolio)
        ->each(function($portfolio) use ($form) {
          $form->setDefault('view', $portfolio->page);
        });
    }

    $event = new stdClass;
    $event->form = $form;
    $event->response = $response;
    events_trigger('assignsubmission_mahara_format_pages', $event);
  }

  /**
   * @see parent
   *
   * @param stdClass $oldsubmission
   * @param stdClass $newsubmission
   * @return boolean
   */
  public function copy_submission(stdClass $oldsubmission, stdClass $newsubmission) {
    $submitted = $this->get_portfolio_record($oldsubmission);

    if ($submitted) {
      $plugin = $this;
      return $this
        ->get_service()
        ->get_portfolio($submitted->portfolio)
        ->map(
          function($portfolio) use ($plugin, $submitted, $oldsubmission, $newsubmission) {
            return $plugin->add_update_portfolio_record($newsubmission, $portfolio, $submitted->status);
          })
        ->getOrElse(true);
    }

    return true;
  }

  /**
   * Internal set error handler
   *
   * @param array $errors
   * @return boolean
   */
  public function on_error_handler($errors) {
    $this->set_error(implode('<br/>', $errors));
    return false;
  }

  /**
   * @see parent
   *
   * @param stdClass $submission
   * @param stdClass $data
   * @return bool
   */
  public function save(stdClass $submission, stdClass $data) {
    // We'll allow a server-side search
    if (
      (!empty($data->search) && empty($data->view)) ||
      empty($data->view)
    ) {
      return false;
    }

    // This is the old submission
    $plugin = $this;
    $release_previous = $this->create_release_callback($submission);

    if ($this->assignment->get_instance()->submissiondrafts) {
      // Check local user portfolio repo, before hitting mahara again
      $local_portfolio = $this->get_service()->get_users_portfolios($submission->userid);
      foreach ($local_portfolio as $portfolio) {
        if ($portfolio->page == $data->view) {
          break;
        }
      }

      // We have a local one, update only
      if (isset($portfolio)) {
        return $release_previous($portfolio);
      } else {
        // We have to hit mahara to get chosen portfolio info
        return $this
          ->get_service()
          ->request_pages_for_user($submission->userid)
          ->fold(
            array($this, 'on_error_handler'),
            function($response) use ($plugin, $submission, $data) {
              foreach ($response['data'] as $page) {
                if ($page['id'] == $data->view){
                  break;
                }

                return $plugin
                  ->get_service()
                  ->get_portfolio($submission->userid, $data->view)
                  ->orElse(
                    function() use ($plugin, $submission, $page) {
                      return $plugin
                        ->get_service()
                        ->add_update_portfolio($submission->userid, (object) $page);
                    })
                  ->map($release_previous)
                  ->getOrElse(false);
              }
            });
      }
    } else {
      return $this
        ->lock_portfolio($submission, $data->view)
        ->fold(
          array($this, 'on_error_handler'),
          function($success) { return $success; });
    }
  }

  /**
   * Releases a single submission
   *
   * @param stdClass $submitted
   * @param int $userid (Optional)
   * @return Model_Option[Model_Either]
   */
  public function release_submission($submitted, $userid = null) {
    $plugin = $this;
    return $plugin
      ->get_service()
      ->get_local_portfolio($submitted->portfolio)
      ->map(
        function($portfolio) use ($plugin, $userid) {
          return $plugin
            ->get_service()
            ->request_release_submitted_view($userid, $portfolio->page)
            ->withRight()
            ->map(function() use ($portfolio) { return $portfolio; });
        });
  }

  /**
   * Callback used for releasing a previously submitted portfolio
   *
   * @param stdClass $submission
   * @return callable ($portfolio => boolean)
   */
  private function create_release_callback($submission) {
    $plugin = $this;

    return function($portfolio, $status = assign_submission_mahara::STATUS_SELECTED) use ($plugin, $submission) {
      $submitted = $plugin->get_portfolio_record($submission);
      $different = ($submitted && $portfolio->id != $submitted->portfolio);
      $success = $plugin->add_update_portfolio_record($submission, $portfolio, $status);
      if ($success) {
        if ($submitted && $submitted->status == $plugin::STATUS_SUBMITTED && $different) {
          $plugin->release_submission($submitted, $submission->userid);
        }
      }

      return $success;
    };

  }

  /**
   * Locks a portfolio and save its submitted status record
   *
   * @param stdClass $submission
   * @param int $viewid
   * @return Model_Either
   */
  public function lock_portfolio($submission, $viewid) {
    $plugin = $this;
    $release_previous = $this->create_release_callback($submission);

    return $this
      ->get_service()
      ->request_submit_page_for_user($submission->userid, $viewid)
      ->withRight()
      ->map(
        function($portfolio) use ($plugin, $release_previous) {
          $success = $release_previous($portfolio, $plugin::STATUS_SUBMITTED);

          // Allow plugins to handle insert / updates (outcomes, etc)
          if ($success) {
            $eventdata = new stdClass;
            $eventdata->portfolio = $portfolio;

            events_trigger('assignsubmission_mahara_submitted_portfolio', $eventdata);
          }

          return $success;
        });
  }

  /**
   * This is only called when the teacher or admin requires it
   *
   * @see parent
   *
   * @param stdClass $submission
   */
  public function submit_for_grading($submission) {
    $plugin = $this;

    $submitted = $this->get_portfolio_record($submission);
    if ($submitted) {
      $this
        ->get_service()
        ->get_local_portfolio($submitted->portfolio)
        ->each(
          function($portfolio) use ($plugin, $submission) {
            $plugin
              ->lock_portfolio($submission, $portfolio->page)
              ->withLeft()
              ->map(
                function($errors) use ($plugin) {
                  $plugin->on_error_handler($errors);
                  print_error('err_mnetclient', 'assignsubmission_mahara', '', $plugin->get_error());
                });
          });
    }
  }

  /**
   * @see parent
   * @return boolean
   */
  public function is_empty(stdClass $submission) {
    $service = $this->get_service();
    $submitted = $this->get_portfolio_record($submission);

    return (
      empty($submitted) ||
      $service
        ->get_local_portfolio($submitted->portfolio)
        ->filter(
          function($portfolio) use ($service) {
            return $portfolio->host == $service->get_host();
          })
        ->isEmpty()
    );
  }

  /**
   * This function solely exists to make sure re-opened
   * submissions are properly released.
   *
   * @see parent
   */
  public static function cron() {
    global $DB, $CFG;

    require_once "{$CFG->dirroot}/mod/assign/locallib.php";

    $cache = array();
    $retrieve_course = function($assign) use (&$cache) {
      global $DB;

      if (isset($cache[$assign->course])) {
        return $cache[$assign->course];
      } else {
        $course = $DB->get_record('course', array('id' => $assign->course), '*', MUST_EXIST);
        $cache[$assign->course] = $course;
      }
      return $cache[$assign->course];
    };

    $sql = "SELECT a.* FROM {assign} a, {assign_plugin_config} c "
      . "WHERE a.id = c.assignment AND c.plugin = 'mahara' AND "
      . "c.subtype = 'assignsubmission' AND "
      . "c.name = 'enabled' AND c.value = 1";

    $mahara_instances = $DB->get_records_sql($sql);
    foreach ($mahara_instances as $instance) {
      $cm = get_coursemodule_from_instance('assign', $instance->id, $instance->course, false, MUST_EXIST);
      $context = context_module::instance($cm->id);

      $assign = new assign($context, $cm, $retrieve_course($instance));
      $mahara = $assign->get_submission_plugin_by_type('mahara');

      $drafts_or_reopens = $mahara->get_all_drafts_or_reopens();
      $submitted_portfolios = $mahara->get_all_submitted(array_keys($drafts_or_reopens));

      foreach ($submitted_portfolios as $submitted) {
        $submission = $drafts_or_reopens[$submitted->submission];
        $submitted->status = $submission->status == ASSIGN_SUBMISSION_STATUS_REOPENED ?
          self::STATUS_RELEASED :
          self::STATUS_SELECTED;

        $mahara
          ->release_submission($submitted)
          ->map(
            function($option) use ($mahara, $submitted, $submission) {
              $option->each(function($portfolio) use ($mahara, $submitted, $submission) {
                $mahara->add_update_portfolio_record($submission, $portfolio, $submitted->status);
              });
            });
      }
    }
  }
}
