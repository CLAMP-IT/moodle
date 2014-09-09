<?php

abstract class mahara_local_events {
  /**
   * Gets the service that handles things
   *
   * @return mahara_mnetservice
   */
  public static function get_service() {
    if (!class_exists('mahara_mnetservice')) {
      require_once dirname(__FILE__) . '/mnetlib.php';
    }

    return new mahara_mnetservice(-1);
  }

  /**
   * Handles when a user is removed from Moodle.
   * Fires the associated 'mahara_portfolio_deleted' event for plugins
   *
   * @param stdClass $user
   * @return boolean
   */
  public static function user_deleted($user) {
    $service = self::get_service();
    $portfolios = $service->get_users_portfolios($user->id);

    foreach ($portfolios as $portfolio) {
      $service->delete_portfolio($portfolio);
    }

    return true;
  }
}
