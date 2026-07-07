/* eslint-disable */
// Helper function to convert bytes to megabytes
export const sizeInMb = sizeInBytes => (sizeInBytes / (1024 * 1024)).toFixed(2);

/**
 * Download table element as a csv.
 * @param {string} table_id
 * @param {string} separator
 */
export function download_table_as_csv(table_id, separator = ',') {
  // Select rows from table_id
  var rows = document.querySelectorAll('table#' + table_id + ' tr');
  // Construct csv
  var csv = [];
  for (var i = 0; i < rows.length; i++) {
    var row = [], cols = rows[i].querySelectorAll('td, th');
    for (var j = 0; j < cols.length; j++) {
      // Clean innertext to remove multiple spaces and jumpline (break csv)
      var data = cols[j].innerText.replace(/(\r\n|\n|\r)/gm, '').replace(/(\s\s)/gm, ' ');

      // Escape double-quote with double-double-quote
      // (see https://stackoverflow.com/questions/17808511/properly-escape-a-double-quote-in-csv)
      data = data.replace(/"/g, '""');

      // If no data is returned, it usually means that there is a font-awesome icon in the table.
      if (data.length === 0) {

        if (cols[j].firstChild?.classList?.contains('text-success')) {
          data = "1"
        } else if (cols[j].firstChild?.classList?.contains('text-danger')) {
          data = "0"
        } else if (cols[j].firstChild?.classList?.contains('text-warning')) {
          data = "?"
        }

      }

      // Push escaped string
      row.push('"' + data + '"');
    }
    csv.push(row.join(separator));
  }
  var csv_string = csv.join('\n');
  // Download it
  var filename = 'export_' + table_id + '_' + new Date().toLocaleDateString() + '.csv';
  var link = document.createElement('a');
  link.style.display = 'none';
  link.setAttribute('target', '_blank');
  link.setAttribute('href', 'data:text/csv;charset=utf-8,' + encodeURIComponent(csv_string));
  link.setAttribute('download', filename);
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
}

// Helper function to render fontawesome success icon
export const renderSuccessIcon = () => '<i class="fa-solid fa-check text-success"></i>';

// Helper function render fontawesome warning icon
export const renderWarningIcon = () => '<i class="fa-solid fa-warning text-warning"></i>';

// Helper function to render fontawesome failure icon
export const renderFailIcon = () => '<i class="fa-solid fa-xmark text-danger"></i>';