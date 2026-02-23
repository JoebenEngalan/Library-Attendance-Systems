$(document).on("click", ".edit_data4", function () {

    $("#edit_id").val($(this).data("id"));
    $("#edstudid").val($(this).data("studid"));
    $("#LName").val($(this).data("lname"));
    $("#FName").val($(this).data("fname"));
    $("#editcourse").val($(this).data("course"));
    $("#edityearlevel").val($(this).data("year"));

    // NEW FIELDS
    $("#editremarks").val($(this).data("remarks") || '');
    $("#editstatus").val($(this).data("status") || 'ACTIVE');

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