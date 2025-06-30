/* eslint-disable */
import $ from 'jquery';
import {exception as displayException} from 'core/notification';
import Templates from 'core/templates';
import ChartJS from 'core/chartjs';
import ModalFactory from 'core/modal_factory';
import {download_table_as_csv, renderFailIcon, renderSuccessIcon} from './util'

const COLOR_PASS = '#198754'
const COLOR_CHECK = '#f0ad4e'
const COLOR_FAIL = '#d9534f'

const LANG_CHECK_URL = 'https://www.adobe.com/accessibility/pdf/pdf-accessibility-overview.html'
const OUTLINE_CHECK_URL = 'https://www.adobe.com/accessibility/pdf/pdf-accessibility-overview.html'
const TITLE_CHECK_URL = 'https://www.adobe.com/accessibility/pdf/pdf-accessibility-overview.html'
const TEXT_CHECK_URL = 'https://www.adobe.com/accessibility/pdf/pdf-accessibility-overview.html'

/**
 * This function acts as the main entry point and renderer for the plugin. It will attach to DOM elements created in
 * the templates/**.mustache, and populate them with data.
 *
 * @param {object} data Information about the scan queue and scan results
 * @param {object} data.totals
 * @param {number} data.totals.scanned How many document have been scanned
 * @param {number} data.totals.inqueue How many documents are waiting to be scanned
 * @param {number} data.totals.notinqueue How many documents are not yet in the queue
 * @param {object} data.pdfs
 * @param {object[]} data.pdfs.pass An array of PDFs that meet 100% of a11y checks performed by scanner
 * @param {object[]} data.pdfs.warn An array of PDFs that meet between 100% and 0% of a11y checks performed by scanner
 * @param {object[]} data.pdfs.fail An array of PDFs that meet 0% of a11y checks performed by scanner
 **/
export const init = (data, courseid) => {

  /**
   * Get the last scanned file from {data}
   * @return string
   */
  function getLastScanned() {
    const allPdfs = [].concat(data.pdfs.pass, data.pdfs.warn, data.pdfs.fail)
    if (allPdfs.length > 0) {
      const max = allPdfs.reduce((a, b) => (+a.lastchecked > +b.lastchecked) ? a : b)
      if (max) {
        const date = new Date(+max.lastchecked * 1000)
        return `Last scanned ${date.toDateString()} at ${date.toLocaleTimeString()}`
      } else {
        return ''
      }
    } else {
      return ''
    }
  }

  /**
   * Create stats breakdown in pie graph and attach to the DOM.
   * Pie slices are pas, warn, and fail.
   */
  function renderPieChart() {

    // Create the canvas element.
    const canvas = $('<canvas/>')
      .attr({
        id: '#block-accessibility-filescan-pie-chart-canvas',
        'aria-label': 'Pie chart describing the accessibility status of this course\'s PDFs.'
      })

    const ctx = canvas[0].getContext('2d')

    // Append the canvas to the DOM.
    $('#block-accessibility-filescan-pie-chart-root')
      .addClass("mb-2 w-100")
      .append(
        $('<h6/>')
          .addClass('mb-2')
          // TODO: Text label should be a string in lang/en/block_accessibility_filescan.php
          .text('Accessibility of Course PDFs')
      )
      .append(canvas)

    const chartData = {
      labels: [
        `Satisfies all checks (${data.pdfs.pass.length})`,
        `Satisfies some checks (${data.pdfs.warn.length})`,
        `Satisfies no checks (${data.pdfs.fail.length})`
      ],
      datasets: [{
        label: 'PDFs',
        data: [
          data.pdfs.pass.length,
          data.pdfs.warn.length,
          data.pdfs.fail.length
        ],
        backgroundColor: [
          COLOR_PASS,
          COLOR_CHECK,
          COLOR_FAIL
        ]
      }]
    }
    new ChartJS(ctx, {
      type: 'pie',
      data: chartData
    })
  }

  function createNoDataParagraph() {
    return $('<p/>')
      .text("No data to show 😢")
  }

  /**
   * Creates details table showing breakdown of stats returned by the scanner.
   * @returns {*|jQuery|void}
   */
  async function createDetailsTable() {

    // Create the table content.
    const $table = $('<table/>')
      .attr('id', 'block-accessibility-filescan-table')
      .addClass('table table-bordered table-hover w-100')
      .append(
        $('<thead/>')
          .addClass('mw-100 w-100')
          .append(
            $('<tr/>')
              .addClass('mw-100 w-100')
              .append(
                $('<th/>')
                  .attr('scope', 'col')
                  .text('Filename')
              )
              .append(
                $('<th/>')
                  .attr('scope', 'col')
                  .text('Lang')
                  .append(
                    $('<i/>')
                      .attr('type', 'button')
                      .attr('class', 'ml-2 fa-solid fa-circle-info text-info')
                      .click(() => {
                        const newWindow = window.open(LANG_CHECK_URL, 'height=600,width=600')
                        if (window.focus) {
                          newWindow.focus()
                        }
                      })
                  )
              )
              .append(
                $('<th/>')
                  .attr('scope', 'col')
                  .text('Text')
                  .append(
                    $('<i/>')
                      .attr('type', 'button')
                      .attr('class', 'ml-2 fa-solid fa-circle-info text-info')
                      .click(() => {
                        const newWindow = window.open(TEXT_CHECK_URL, 'height=600,width=600')
                        if (window.focus) {
                          newWindow.focus()
                        }
                      })
                  )
              )
              .append(
                $('<th/>')
                  .attr('scope', 'col')
                  .text('Title')
                  .append(
                    $('<i/>')
                      .attr('type', 'button')
                      .attr('class', 'ml-2 fa-solid fa-circle-info text-info')
                      .click(() => {
                        const newWindow = window.open(TITLE_CHECK_URL, 'height=600,width=600')
                        if (window.focus) {
                          newWindow.focus()
                        }
                      })
                  )
              )
              .append(
                $('<th/>')
                  .attr('scope', 'col')
                  .text('Tagged')
                  .append(
                    $('<i/>')
                      .attr('type', 'button')
                      .attr('class', 'ml-2 fa-solid fa-circle-info text-info')
                      .click(() => {
                        const newWindow = window.open(OUTLINE_CHECK_URL, 'height=600,width=600')
                        if (window.focus) {
                          newWindow.focus()
                        }
                      })
                  )
              )
              .append(
                $('<th/>')
                  .attr('scope', 'col')
                  .text('Pages')
              )
          )
      )
    // Create the table body.
    const $tableBody = $('<tbody/>')

    // Concatenate an array with all pdfs in data arg.
    const pdfs = [].concat(
      data.pdfs.pass,
      data.pdfs.warn,
      data.pdfs.fail
    )

    // Generate the table rows
    const $tableRows = pdfs.map(
      pdf => $('<tr/>')
        .addClass('w-100')
        .append(
          $('<td/>')
            .addClass('text-truncate')
            .attr('style', 'max-width: 300px;')
            .append(
              $('<a/>')
                .attr('href', pdf.url)
                .text(pdf.filename)
            )
        )
        .append(
          $('<td/>')
            .append(pdf.haslanguage === "1"
              ? $(renderSuccessIcon())
              : $(renderFailIcon()))
        )
        .append(
          $('<td/>')
            .append(pdf.hastext === "1"
              ? $(renderSuccessIcon())
              : $(renderFailIcon()))
        )
        .append(
          $('<td/>')
            .append(pdf.hastitle === "1"
              ? $(renderSuccessIcon())
              : $(renderFailIcon()))
        )
        .append(
          $('<td/>')
            .append(pdf.istagged === "1"
              ? $(renderSuccessIcon())
              : $(renderFailIcon()))
        )
        .append(
          $('<td/>')
            .append(pdf.pagecount >= 1
              ? $('<span/>').text(pdf.pagecount)
              : $(renderFailIcon()))
        )
    )

    // Append the table rows to the table.
    $tableBody.append($tableRows)
    $table.append($tableBody)

    return $table
  }

  /**
   * Creates a download button that when clicked, downloads a CSV version of the table.
   * @returns {*|jQuery|void}
   */
  function createDownloadButton() {
    // Create the download to csv button.
    return $('<button/>')
      .addClass('btn btn-secondary mb-2')
      .text('Download to CSV')
      .click(() => download_table_as_csv('block-accessibility-filescan-table'))
  }

  /**
   * Create the button element that when clicked, opens the modal.
   * @param modal
   * @returns {*|void}
   */
  function createModalTriggerButton(modal) {
    // Create the button triggering the modal.
    return $('<button/>')
      .addClass('btn btn-secondary')
      .text('Details')
      .click(() => {
        // Dispatch event to invalidate the block's cache
        //dispatchEvent("\\block_a11y_check\\event\\invalidate_results_cache" , { courseid: +courseid })
        // Show the modal
        modal.show()
      })
  }

  /**
   * Creates a modal window that displays a table of each PDF scanned by the plugin in the current course.
   * @returns {Promise<*>}
   */
  async function createModal() {

    const $table = await createDetailsTable()

    // Create the modal body first, so we can attach it during the modal's instantiation.
    const $modalBody = $('<div/>')
      .append(createDownloadButton($table))
      .append(
        $('<div/>')
          .addClass('table-responsive')
        )
        .append($table)

    // Create the modal.
    const modal = await ModalFactory.create({
      title: 'Accessibility of Course PDFs - Details',
      body: $modalBody,
      footer: $('<p/>')
        .text(
          getLastScanned()
        ),
      large: true
    })

    // This allows the modal to have 95% of screen width.
    modal.getRoot()[0].childNodes[1].style.maxWidth = '95vw' || undefined

    return modal
  }

  // Create the context. This gets passed to the template.
  const context = {
    name: 'A11y Check'
  };

  // This will call the function to load and render our template.
  Templates.renderForPromise('block_accessibility_filescan/summary', context)
    .then(({html, js}) => {

      // Render the template.
      Templates.appendNodeContents("#block-accessibility-filescan-root", html, js);

      // Create the modal.
      createModal()
        .then(modal => {

          // Render the queue stats.
          if (data.pdfs.pass.length > 0 || data.pdfs.warn.length > 0 || data.pdfs.fail.length > 0) {
            // Render the pie chart.
            renderPieChart()

            // Append the button trigger to the DOM.
            $("#block-accessibility-filescan-more-details-root")
              .addClass('mb-2')
              .append(
                createModalTriggerButton(modal)
              )
              .append(
                $('<p/>')
                  .addClass('mt-3')
                  .append(
                    $('<small/>')
                      .text(
                        getLastScanned()
                      )
                  )
              )
          } else {
            $('#block-accessibility-filescan-root').append(
              createNoDataParagraph()
            )
          }

        }).catch((error) => displayException(error));

    })
    // Deal with this exception (Using core/notify exception function is recommended).
    .catch((error) => displayException(error));
};
