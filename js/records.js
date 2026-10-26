$(document).on("click", ".edit_data4", function () {
    // Rows are built by DataTables, so read the data from the row, not from data-* attributes
    const row = $('#recordTable').DataTable().row($(this).closest('tr')).data();
    if (!row) return;

    $("#edit_id").val(row.id);
    $("#edstudid").val(row.studID);
    $("#LName").val(row.Lname);
    $("#FName").val(row.Fname);
    $("#editcourse").val(row.Course);
    $("#edityearlevel").val(row.YearLevel);
    $("#editremarks").val(row.remarks || '');
    $("#editstatus").val(row.status || 'ACTIVE');
});

// Replaces the old inline onsubmit="return confirm(...)" on each Ready form
$(document).on("submit", ".mark-ready-form", function () {
    return confirm('Mark this record as Ready for approval?');
});

document.addEventListener("DOMContentLoaded", function() {
    // When the modal closes → reset all fields inside the form
    const editModal = document.getElementById('EditSudent');
    editModal.addEventListener('hidden.bs.modal', function () {
        // reset all inputs inside the modal
        editModal.querySelector('form').reset();
    });
});

document.addEventListener("DOMContentLoaded", function () {
    const tooltipTriggerList = [].slice.call(
        document.querySelectorAll('[data-bs-toggle="tooltip"]')
    );
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});