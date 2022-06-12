$(function() {
    const customs = document.querySelectorAll('.custom-field button');
    for (i = 0; i < customs.length; i++) {
        customs[i].addEventListener('click', function () {
            const fieldName = $(this).parent().parent().find('.custom-input').data('fieldname');
            const baseUrl = $(this).parent().parent().find('.custom-input').data('baseurl');
            const value = $(this).parent().parent().find('.custom-input').val();

            const url = baseUrl
                + "&entityId=" + this.closest('tr').dataset.id
                + "&fieldName=" + fieldName
                + "&newValue=" + value;

            let request = $.ajax({type: "GET", url: url, data: {}});

            request.done(function (result) {
            });

            request.fail(function () {
                // in case of error, restore the original value and disable the toggle
                // customSwitch.checked = oldValue;
                // customSwitch.disabled = true;
                // customSwitch.closest('.custom-switch').classList.add('disabled');
            });
        });
    }
});