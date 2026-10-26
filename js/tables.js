
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

        const escHtml = s => String(s ?? '').replace(/[&<>"']/g, c => (
            { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]
        ));
        const badgeMap = { ACTIVE: 'success', SUSPENDED: 'danger', INACTIVE: 'secondary', GRADUATED: 'primary' };

        // text column: escaped for display, raw for sort/filter/export
        const textCol = (field, extra = {}) => Object.assign({
            data: field,
            render: (d, type) => type === 'display' ? escHtml(d) : (d ?? '')
        }, extra);

        // Excel/PDF/CSV/Print: skip the Action column and export plain values
        const exportOpts = { exportOptions: { columns: ':not(:last-child)', orthogonal: 'export' } };

        new DataTable(recordTableId, {
            ajax: 'ajax/student_records.php',
            deferRender: true,
            pageLength: 25,
            lengthMenu: [10, 25, 50, 100],
            order: [[6, 'desc']],               // latest updated first

            columns: [
                textCol('studID', { className: 'text-center' }),
                textCol('Lname',  { className: 'text-start' }),
                textCol('Fname',  { className: 'text-start' }),
                textCol('YearLevel'),
                textCol('Course'),
                {
                    data: 'status',
                    render: (d, type) => type === 'display'
                        ? `<span class="badge bg-${badgeMap[d] || 'dark'}">${escHtml(d)}</span>`
                        : (d ?? '')
                },
                {
                    data: 'updated_at',
                    render: (d, type, row) => (type === 'display' || type === 'export')
                        ? (row.updated_fmt || '-')
                        : (d ?? '')
                },
                {
                    data: 'remarks',
                    render: (d, type) => type === 'display' ? escHtml(d || '-') : (d ?? '')
                },
                {
                    data: null, orderable: false, searchable: false,
                    render: (d, type, row) => `
                        <div class="d-flex gap-1 justify-content-center">
                            <a href="#" class="edit_data4 btn btn-sm btn-secondary"
                               data-bs-toggle="modal" data-bs-target="#EditSudent">
                                <i class="fa-duotone fa-solid fa-pen-to-square"></i>
                                <span class="d-none d-sm-inline"> Edit</span>
                            </a>
                            <form method="POST" class="d-inline mark-ready-form">
                                <input type="hidden" name="id" value="${Number(row.id)}">
                                <button type="submit" name="markReady" class="btn btn-sm btn-success"
                                        title="Add 'Ready for approval' to remarks">
                                    <i class="fa-duotone fa-solid fa-circle-check"></i>
                                    <span class="d-none d-sm-inline"> Ready</span>
                                </button>
                            </form>
                        </div>`
                }
            ],

            layout: {
                topStart: {
                    buttons: [
                        {
                            extend: 'searchPanes',
                            className: 'btn btn-info text-nowrap',
                            text: '<i class="fa-thin fa-filter"></i> <span class="d-none d-sm-inline">Filter</span>',
                            config: { columns: [3, 4, 5] }   // Year Level, Course, Status
                        },
                        Object.assign({
                            extend: 'excel',
                            className: 'btn btn-success text-nowrap',
                            text: '<i class="fa-thin fa-file-excel"></i> <span class="d-none d-sm-inline">Excel</span>'
                        }, exportOpts),
                        Object.assign({
                            extend: 'pdf',
                            className: 'btn btn-danger text-nowrap',
                            text: '<i class="fa-thin fa-file-pdf"></i> <span class="d-none d-sm-inline">PDF</span>'
                        }, exportOpts),
                        Object.assign({
                            extend: 'csv',
                            className: 'btn btn-warning text-nowrap',
                            text: '<i class="fa-thin fa-file-csv"></i> <span class="d-none d-sm-inline">CSV</span>'
                        }, exportOpts),
                        Object.assign({
                            extend: 'print',
                            className: 'btn btn-secondary text-nowrap',
                            text: '<i class="fa-thin fa-print"></i> <span class="d-none d-sm-inline">Print</span>'
                        }, exportOpts)
                    ]
                }
            },

            language: {
                emptyTable: "No student records found",
                zeroRecords: "No matching student records"
            }
        });
    }

});
