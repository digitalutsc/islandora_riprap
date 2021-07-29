<?php

namespace Drupal\islandora_riprap\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Controller.
 */
class IslandoraRiprapFileEventsController extends ControllerBase
{
  /**
   * Constructor.
   */
  public function __construct()
  {
    $config = \Drupal::config("islandora_riprap.settings");
    $this->riprap_endpoint =
      $config->get("riprap_rest_endpoint") ?:
      "http://localhost:8000/api/fixity";
    $this->number_of_events = $config->get("number_of_events") ?: 10;
    $this->use_drupal_urls = $config->get("use_drupal_urls") ?: false;
    $this->show_warnings = !$config->get("show_riprap_warnings")
      ? $config->get("show_riprap_warnings")
      : true;
  }

  /**
   * Get the Riprap data for the current File entity and render it.
   *
   * @return array
   *   Themed markup.
   */
  public function main()
  {
    $riprap = \Drupal::service("islandora_riprap.riprap_files");
    $current_path = \Drupal::service("path.current")->getPath();
    $path_args = explode("/", $current_path);
    $fid = $path_args[2];

    $binary_resource_uuid = $riprap->getFileUuid($fid);
    if ($this->use_drupal_urls) {
      $binary_resource_url = $riprap->getLocalUrl($fid, false);
    } else {
      $binary_resource_url = $riprap->getFedoraUrl($fid);
    }

    $riprap_output = $riprap->getEvents([
      "output_format" => "json",
      "resource_id" => $binary_resource_url,
    ]);

    if (!$riprap_output) {
      \Drupal::messenger()->addMessage(
        $this->t(
          "Cannot retrieve fixity events from Riprap for this file. This has been logged, but please contact the system administrator."
        ),
        "error"
      );
      \Drupal::logger("islandora_riprap")->error(
        $this->t(
          "Riprap expected to get fixity event information for @url (File @fid) but cannot.",
          ["@url" => $binary_resource_url, "@fid" => $fid]
        )
      );
      return [];
    }

    $riprap_output = json_decode($riprap_output, true);
    if ($this->show_warnings) {
      if (
        count($riprap_output) == 0 &&
        $binary_resource_url != "Not in Fedora"
      ) {
        \Drupal::messenger()->addMessage(
          $this->t(
            "No fixity event information for @binary_resource_url (File @fid).",
            ["@binary_resource_url" => $binary_resource_url, "@fid" => $fid]
          ),
          "warning"
        );
        return [];
      }
    }

    $header = [
      t("Event UUID"),
      t("Resource URI"),
      t("Event type"),
      t("Timestamp"),
      t("Digest algorithm"),
      t("Digest value"),
      t("Event detail"),
      t("Event outcome"),
      t("Note"),
      t('Matches FITS (Y/N)'),
    ];
    $rows = [];
    foreach ($riprap_output as &$event) {
      $match_fits = ($event['event_outcome'] == "success") ? "Y" : "N";
      array_push($event, ($event['event_outcome'] == "success") ? "Y" : "N");
      $rows[] = array_values($event);
    }

    $output = [
      "#theme" => "table",
      "#header" => $header,
      "#rows" => $rows,
    ];

    return [
      "#theme" => "islandora_riprap_file_events",
      "#report" => $output,
      "#fid" => $fid,
      "#binary_resource_url" => $binary_resource_url,
    ];
  }
}
