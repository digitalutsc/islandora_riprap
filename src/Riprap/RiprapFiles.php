<?php

namespace Drupal\islandora_riprap\Riprap;

use Drupal\file\Entity\File;
use Drupal\Core\Site\Settings;
use Drupal\Core\Link;
use Symfony\Component\Process\Process;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Utilities for interacting with a Riprap fixity microservice.
 */
class RiprapFiles extends Riprap
{
  /**
   * Given a File id, get the File's UUID.
   *
   * @param int $fid
   *   A File ID.
   *
   * @return string
   *   The UUID of the file associated with the incoming file entity.
   */
  public function getFileUuid($fid)
  {
    $file = File::load($fid);
    // return $file->getFileUuid();
    return $file->uuid();
  }

  /**
   * Given a File id, get the File's local Drupal URL.
   *
   * Used for files that are not stored in Fedora.
   *
   * @param int $fid
   *   A File ID.
   *
   * @param bool $return_url
   *   TRUE returns the file's http URL, FALSE returns the Drupal URI (e.g. public://).
   *
   * @return string
   *   The local Drupal URL of the file associated with the
   *   incoming File entity.
   */
  public function getLocalUrl($fid, $return_url = true)
  {
    $file = File::load($fid);
    $uri = $file->getFileUri();
    if ($return_url) {
      $url = file_create_url($uri);
      return $url;
    } else {
      return $uri;
    }
  }
}
