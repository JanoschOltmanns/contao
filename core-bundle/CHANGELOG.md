# Contao core bundle change log

### 4.1.1 (2015-03-03)

 * Remove the "disable IP check" field from the back end settings (see #436).
 * Do not quote the search string in `FIND_IN_SET()` (see #424).
 * Always fix the domain and language when generating URLs (see contao/core#8238).
 * Fix two issues with the flexible back end theme (see contao/core#8227).
 * Correctly toggle custom page type icons (see contao/core#8236).
 * Correctly render the links in the monthly/yearly event list menu (see contao/core#8140).
 * Skip the registration related fields if a user is duplicated (see contao/core#8185).
 * Correctly show the form field type help text (see contao/core#8200).
 * Correctly create the initial version of a record (see contao/core#8141).
 * Correctly show the "expand preview" buttons (see contao/core#8146).
 * Correctly check that a password does not match the username (see contao/core#8209).
 * Check if a directory exists before executing `mkdir()` (see contao/core#8150).
 * Do not link to the maintenance module if the user cannot access it (see contao/core#8151).
 * Show the "new folder" button in the template manager (see contao/core#8138).

### 4.1.0 (2015-11-26)

 * Log e-mails in the database instead of a log file (see #413).
 * Use events to modify the front end preview URL.
 * Handle closures in the back end help controller (see #408).
 * Fix saving the image size widget if no option is selected (see #411).
 * Correctly set the ID when toggling fields via Ajax (see contao/core#8043).

### 4.1.0-RC1 (2015-11-10)

 * Limit access to the image sizes per user or user group (see #319).
 * Support the Lexik maintenance bundle if it is installed (see #283).
 * Throw an exception instead of redirecting to `/contao?act=error` (see #395).
 * Make the image caching and target path configurable (see #381).
 * Call the load_callback when loading the page/file tree via ajax (see #398).
 * Load the random_compat library in the Composer script handler (see #397).
 * Never cache a page if there are messages (see #343).
 * Adjust the code to be compatible with PHP7 (see contao/core#8018).
 * Fix several issues with the new file search and add the `type:file` and `type:folder` flags (see #392).
 * Only warm the Contao cache if the installation has been completed (see #383).
 * Support retrieving services in `System::import()` and `System::importStatic()` (see #376).
 * Handle an empty input in the meta wizard (see #382).
 * Use the `kernel.terminate` event for the command scheduler (see #244).
 * Support news and event links in the front end preview (see contao/core#7504).

### 4.1.0-beta1 (2015-10-21)

 * Add all translations which are at least 95% complete.
 * Update the hash of an existing file in `Dbafs::addResource()` (see contao/core#7828).
 * Add a more accurate e-mail validation with unicode support (see #367).
 * Add the "env::base_url" insert tag (see #322).
 * Make the file manager and file picker searchable (see contao/core#7196).
 * Show the website title in the back end (see contao/core#7840).
 * Re-send the activation mail upon a second registration attempt (see contao/core#7992).
 * Always show the "save and edit" button (see contao/core#3567).
 * Highlight the rows on hover via CSS (see contao/core#7837).
 * Show the important part in the file tree (see contao/core#7865).
 * Also show the field names in "edit multiple" mode (see contao/core#7868).
 * Add a button to remove single images from the file tree selection (see contao/core#6684).
 * Support overwriting resources in `app/Resources/contao` (see #314).