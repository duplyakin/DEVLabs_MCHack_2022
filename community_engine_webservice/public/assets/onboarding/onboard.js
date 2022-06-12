$(function () {
    let last = [];
    $('[type=checkbox]').on('change', function (e) {
        let question = e.target.dataset.question,
            group = $('[type=checkbox][data-question=' + question + ']'),
            active = group.parent().find('input:checked');

        if (active.length > maxSelect) {
            console.log(last);
            $(last[question]).prop('checked', false).parent().removeClass('active');
        }

        if ($(e.target).is(':checked')) {
            last[question] = e.target;
        }
    }).trigger('change');

    $(document).on('click', '.upload', (e) => {
        $('#profile-picture').click();
    });

    $(document).on('change', '#profile-picture', (e) => {
        savePhoto(e.target);
    });

    function savePhoto(inputFile) {
        let file = inputFile.files[0];
        let data = new FormData();
        let error = $('.error-photo');
        let picture = $('.profile-edit-avatar');
        let loader = $('.img-loader');
        data.append('photo', file, file.name);
        error.text('').hide();
        picture.removeClass('error');
        loader.show();
        picture.css('opacity', 0.3);
        $.ajax({
            url: "/user/profile/photo-upload",
            type: "POST",
            responseType: 'json',
            data: data,
            processData: false,
            contentType: false,
            cache: false,
            success: (response) => {
                loader.hide();
                picture.css('opacity', 1);
                if (response.url !== undefined) {
                    picture.css('background-image', 'url("' + response.url + '")');
                    error.text('').hide();
                    picture.removeClass('error');
                } else {
                    error.text(response.errorText).show();
                    picture.addClass('error');
                }
            },
            error: (response) => {
                loader.hide();
                picture.css('opacity', 1);
                error.text('File size should not exceed 1 Mb');
                picture.addClass('error');
            },
        });
    }

    $("#question-form").validate({
        errorPlacement: function (error, element) {
            $(element).closest('.card').find('.error-box').html(error);
            return true;
        },
    });

    let profileValidator = $('.profile-page form').validate({
        errorClass: 'text-danger',
        highlight: function (element) {
            $(element).addClass("alert-danger border-danger");
        },
        unhighlight: function (element) {
            $(element).removeClass("alert-danger border-danger");
        },
        lang: 'ru',
        rules: {
            'user[firstName]': {
                required: true,
                minlength: 3
            },
            'user[lastName]': {
                required: true,
                minlength: 3
            },
            'user[emailAlt]': {
                email: true,
                required: req,
                remote: {
                    url: "/user/profile/check-email",
                    type: "post",
                    data: {
                        email: function () {
                            return $("#email-alt-field").val();
                        },
                        token: skey
                    }
                }
            },
            'user[about]': {
                required: true
            }
        },
    });

    $(document).on('submit', '.profile-page form', function () {
        let errors = {
            'user[facebookLink]': "Please fill out one of the social networks fields",
            'user[linkedinLink]': "Please fill out one of the social networks fields",
        };
        if ($('[name="user[facebookLink]"]').val() === '' && $('[name="user[linkedinLink]"]').val() === '') {
            profileValidator.showErrors(errors);
            profileValidator.focusInvalid();
            return false;
        }
    });
});