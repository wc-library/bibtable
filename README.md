# bibtable #
A formated and sortable table referencing  data from Zotero

## Setup ##
1. Put all files and folders into an Apache Server that is running PHP
2. Create a php file named `api_key.php` in the same folder as the files on Apache Server.
 * Look in config folder for an example

### Applying to different html pages ###
* `dynData.js` creates a table and appends it to a table named **myTable** on `test.html`.
* For now, when adding a table produced by `dynData.js` just create a table with id **myTable**.