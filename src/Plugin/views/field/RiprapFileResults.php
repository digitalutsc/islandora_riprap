<?php

namespace Drupal\islandora_riprap\Plugin\views\field;

use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;

/**
 * Field plugin that renders data for File from Riprap.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("riprap_file_results")
 */
class RiprapFileResults extends FieldPluginBase
{
    /**
     * Leave empty to avoid a query on this field.
     *
     * @{inheritdoc}
     */
    public function query()
    {
        // I am empty.
    }

    /**
     * Used for sorting the events from Riprap by decesending time
     */

    /**
     * {@inheritdoc}
     */
    public function render(ResultRow $value)
    {
        $config = \Drupal::config("islandora_riprap.settings");
        $this->use_drupal_urls = $config->get("use_drupal_urls") ?: false;

        $riprap = \Drupal::service("islandora_riprap.riprap_files");

        $file = $value->_entity;
        $fid = $file->id();

        if ($this->use_drupal_urls) {
            $binary_resource_url = $riprap->getLocalUrl($fid, false);
        } else {
            $binary_resource_url = $riprap->getFedoraUrl($fid);
            if (!$binary_resource_url) {
                return [
                    "#theme" => "islandora_riprap_file_summary",
                    "#content" => "Not in Fedora",
                    "#outcome" => null,
                    "#fid" => null,
                ];
            }
        }

        $num_events = $config->get("number_of_events") ?: 10;
        $riprap_output = $riprap->getEvents([
            "limit" => $num_events,
            "output_format" => "json",
            "resource_id" => $binary_resource_url,
        ]);
        $events = json_decode($riprap_output, true);
        print_r($events);

        // Look for events with an 'event_outcome' of 'fail'.
        $failed_events = 0;
        if ($events) {
            foreach ($events as $event) {
                if ($event["event_outcome"] == "fail") {
                    $failed_events++;
                }
            }
        }

        // Set flag in markup so that our Javascript can set the color.
        if ($binary_resource_url == "Not in Fedora") {
            $outcome = "notinfedora";
            $fid = null;
        } else {
            if ($events) {
                $last_event = end($events);
                if ($last_event["event_outcome"] == "fail") {
                    $outcome = "fail";
                } else {
                    $outcome = "success";
                }
            }
        }

        if (!$events) {
            $outcome = "noevents";
            // Show fid and indicate that file is not in
            // Riprap (e.g., 'No Riprap events for $fid').
            $binary_resource_url =
                "No Riprap events for " . $binary_resource_url;

            return [
                "#theme" => "islandora_riprap_file_summary",
                "#content" => $binary_resource_url,
                "#outcome" => null,
                "#fid" => null,
            ];
        }

        // Not a Riprap event, but output that indicates Riprap
        // is not available at its configured endpoint URL.
        if (
            array_key_exists("riprap_status", $events) &&
            $events["riprap_status"] == 404
        ) {
            $binary_resource_url = $events["message"];
            $fid = null;
            $outcome = "riprapnotfound";
        }

        return [
            "#theme" => "islandora_riprap_file_summary",
            "#content" => $binary_resource_url,
            "#outcome" => $outcome,
            "#fid" => $fid,
        ];
    }
}
