/* global comboChart, billID */

$(document).ready(function () {
                      "use strict";
                      if (typeof(comboChart) === 'function' && typeof(billID) !== 'undefined') {
                          comboChart('chart/bill/' + billID, 'bill-overview');
                      }
                  }
);