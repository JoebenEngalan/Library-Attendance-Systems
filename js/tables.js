
// Main attendance table
new DataTable('#maindattables', {
    pageLength: 15
});

new DataTable('#activitytables', {
    pageLength: 5
});

new DataTable('#usertables', {
    pageLength: 5
});

new DataTable('#datatablesSimple', {
    pageLength: 10
});

// Course table
new DataTable('#coursetable', {
    pageLength: 5
});


document.addEventListener("DOMContentLoaded", function () {
    const tableId = '#maindattables';

    if (!$.fn.DataTable.isDataTable(tableId)) {
        new DataTable(tableId, {
            order: [[0, 'desc']], // MAIN ORDER
            pageLength: 10,
            language: {
                emptyTable: "No activity records found",
                zeroRecords: "No matching activity found"
            }
        });
    }
});



document.addEventListener("DOMContentLoaded", function () {
    const tableId = '#datatablesSimple';
    if (!$.fn.DataTable.isDataTable(tableId)) {
        new DataTable('#datatablesSimple', {
            order: [[4, 'desc']],
            pageLength: 10,
            language: {
                emptyTable: "No activity records found",
                zeroRecords: "No matching activity found"
            }
        });
    }
});


document.addEventListener("DOMContentLoaded", function () {
    const tableId = '#usertables';
    if (!$.fn.DataTable.isDataTable(tableId)) {
        new DataTable(tableId, {
            order: [[4, 'desc']],
            pageLength: 10,
            language: {
                emptyTable: "No activity records found",
                zeroRecords: "No matching activity found"
            }
        });
    }
});


document.addEventListener("DOMContentLoaded", function () {
    const tableId = '#coursetable';
    if (!$.fn.DataTable.isDataTable(tableId)) {        
        new DataTable('#coursetable', {
            language: {
                emptyTable: "No activity records found",
                zeroRecords: "No matching activity found"
            }
        });
    }
});

document.addEventListener("DOMContentLoaded", function () {

/* ================= AttendTable ================= */

    const attendTableId = '#AttendTable';

    if (!$.fn.DataTable.isDataTable(attendTableId)) {
        new DataTable(attendTableId, {

            layout: {
                topStart: {
                    buttons: [
                        {
                            extend: 'searchPanes',
                            className: 'btn btn-info text-nowrap',
                            text: '<i class="fa-thin fa-filter"></i> <span class="d-none d-sm-inline">Filter</span>',
                            config: {
                                columns: [5, 6, 7] // Course, YearLevel, MonthYear
                            }
                        },
                        {
                            extend: 'excel',
                            className: 'btn btn-success text-nowrap',
                            text: '<i class="fa-thin fa-file-excel"></i> <span class="d-none d-sm-inline">Excel</span>'
                        },
                        {
                            extend: 'pdf',
                            className: 'btn btn-danger text-nowrap',
                            text: '<i class="fa-thin fa-file-pdf"></i> <span class="d-none d-sm-inline">PDF</span>'
                        },
                        {
                            extend: 'csv',
                            className: 'btn btn-warning text-nowrap',
                            text: '<i class="fa-thin fa-file-csv"></i> <span class="d-none d-sm-inline">CSV</span>'
                        },
                        {
                            extend: 'print',
                            className: 'btn btn-secondary text-nowrap',
                            text: '<i class="fa-thin fa-print"></i> <span class="d-none d-sm-inline">Print</span>'
                        }
                    ]
                }
            },

            // 🔥 VERY IMPORTANT PART
            columnDefs: [
                // ❌ Disable SearchPanes for unsafe columns
                { targets: [0,1,2,3,4], searchPanes: { show: false } },

                // ✅ MonthYear column (hidden but filterable)
                {
                    targets: 7,
                    visible: false,
                    searchPanes: {
                        show: true,
                        name: 'Month'
                    }
                }
            ],

            order: [[2, 'desc']], // Date In
            language: {
                emptyTable: "No activity records found",
                zeroRecords: "No matching activity found"
            }
        });
    }


    /* ================= recordTable ================= */
    const recordTableId = '#recordTable';
    if (!$.fn.DataTable.isDataTable(recordTableId)) {
        new DataTable(recordTableId, {
            layout: {
                topStart: {
                    buttons: [
                        {
                            extend: 'searchPanes',
                            className: 'btn btn-info text-nowrap',
                            text: '<i class="fa-thin fa-filter"></i> <span class="d-none d-sm-inline">Filter</span>'
                        },
                        {
                            extend: 'excel',
                            className: 'btn btn-success text-nowrap',
                            text: '<i class="fa-thin fa-file-excel"></i> <span class="d-none d-sm-inline">Excel</span>'
                        },
                        {
                            extend: 'pdf',
                            className: 'btn btn-danger text-nowrap',
                            text: '<i class="fa-thin fa-file-pdf"></i> <span class="d-none d-sm-inline">PDF</span>'
                        },
                        {
                            extend: 'csv',
                            className: 'btn btn-warning text-nowrap',
                            text: '<i class="fa-thin fa-file-csv"></i> <span class="d-none d-sm-inline">CSV</span>'
                        },
                        {
                            extend: 'print',
                            className: 'btn btn-secondary text-nowrap',
                            text: '<i class="fa-thin fa-print"></i> <span class="d-none d-sm-inline">Print</span>'
                        }
                    ]
                }
            },
            
            columnDefs: [
            { targets: [1,2], orderable: false }
            ],

            language: {
                emptyTable: "No attendance records found for today",
                zeroRecords: "No matching attendance records"
            }
        });
    }

});
