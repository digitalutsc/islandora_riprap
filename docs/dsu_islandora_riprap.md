

# Islandora Riprap Module

Extended off [Mark Jordan's Islandora Riprap Module](https://github.com/mjordan/islandora_riprap)

## Installation Procedure
1. Git clone the module repo 
1. Enable the module through the extend menu
1. Go to Drupal's "Configuration" menu.
   1. In the "System" section, click on the "Fixity auditing" link.
   1. Adjust your config options.
   1. For local mode, set the following:
       - *Absolute Path to Riprap Installation Dir*: /var/www/drupalvm/riprap
       - *Absolute Path to the YAML settings file*: /var/www/drupalvm/riprap/sample_islandora_config_fetch_digest_from_drupal.yml
   1. Select `Execute Riprap during Drupal cron runs. Only applies to "local" mode.` for riprap to run everytime cron is executed
1.  Add the "Fixity Auditing" field to the "Files" View (like you would add any other field to a view):
    1. In your list of Views ("Admin > Structure > Views"), click on the "Edit" button for the "Files" View.
    1. In the "Page" display, click on the "Add" Fields button.
    1. From the list of fields, check "Fixity Auditing".
    1. Click on "Apply (this display)".
    1. Change the label if you want.
    1. Click on "Apply (this display)".
    1. Optionally, you can locate the new "Fixity Auditing" field to any position you want in the Media table.
    1. Click on the "Save" button to save the change to the View.

---

### New HTML Twig Files

*islandora-riprap-file-events.html.twig*  
HTML twig file to display Riprap events for each file (i.e. `/file/{fid}/riprap`)  
  
*islandora-riprap-file-summary.html.twig*  
HTML twig file to display the fixity auditing column in the admin file page (i.e. `/admin/content/files`)


### List of new classes

| Class | Extends/Parent Class | Type | Description |
| ----------- | ----------- | ----------- | ----------- |
| `IslandoraRiprapFileEventsController` | `ControllerBase` | Controller | Controller for the Islandora Riprap module customized for file ID|
| `RiprapFileResults` | `FieldPluginBase` | Field Plugin | Field plugin that renders data for File from Riprap | 
| `RiprapFiles` | `Riprap` | Utility for interacting with the Riprap fixity microservice | Extends the native Riprap class and overrides the `getFileUuid()` and `getLocalUrl()` methods from the parent class | 

### islandora_riprap.module
Extra coded added to apply JS for the file view (i.e. `/admin/content/file`)  
  
    if (
        count($path_args) >= 3 &&
        ($path_args[2] == "media" || $path_args[2] == "files")
        ) {
            $attachments["#attached"]["library"][] =
            "islandora_riprap/islandora_riprap_media";
        }


### islandora_riprap.routing.yml  
New routing path for the Riprap event page for each file
  
    islandora_riprap.file_events:
        path: "/file/{file}/riprap"
         defaults:
            _controller: '\Drupal\islandora_riprap\Controller\IslandoraRiprapFileEventsController::main'
            _title: "Fixity Events (File)"
        requirements:
            _permission: "manage file"
        file: \d+


### islandora_riprap.views.inc
New field added to allow the *fixity auditing* field to show up in the table  

    $data['file_managed']['riprap_results'] = [
        'title' => t('Fixity auditing'),
        'help' => t('Show results from the Riprap fixity auditing microservice.'),
        'field' => [
        'id' => 'riprap_file_results',
        ],
    ];

### islandora_riprap.services.yml
New service `islandora_riprap.riprap_files` defined for the interaction of files and the Riprap fixity microservice  
  
    islandora_riprap.riprap_files:
        class: Drupal\islandora_riprap\Riprap\RiprapFiles
