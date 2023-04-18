function notify(msg, type) {
    $.toast({
        heading: type[0].toUpperCase() + type.slice(1) + ' Message',
        text: msg,
        showHideTransition: 'slide',
        position: 'top-right',
        icon: type
    })
}

$("form").submit(function(e) {
    if ($(this).attr('id') != undefined && $(this).attr('id') != "") {
        return false;
    }
});

function showErrors(errors, doc = null, section = null) {
    let message = "Internal Server Error";

    if (errors.status == 400) {
        message = errors.responseJSON.status;
    } else if (errors.status == 300) {
        message = errors.responseJSON.status;
    } else if (errors.status == 500 || errors.status == 419) {
        message = errors.responseJSON.message;
    } else {
        message = errors.statusText;
    }

    switch (section) {
        case 'dropzone':
            $(doc.previewElement).addClass("dz-error").find('.dz-error-message').text(errors.status ? errors.status : errors.message);
            break;
        default:
            notify(message, "error");
            break;
    }
}

$('.eye-password').on('click', function() {
    var closestinput = $(this).closest('.input-group-btn').closest('.input-group').find('input');
    var type = (closestinput).attr('type');

    if (type == 'password') {
        $(closestinput).attr('type', 'text');

        $(this).html('<i class="fa fa-eye-slash"></i>');
    } else {
        $(closestinput).attr('type', 'password');

        $(this).html('<i class="fa fa-eye"></i>');
    }
});

$('.select2').select2();

$(function() {
    $(document).on('click', '[data-toggle="lightbox"]', function(event) {
        event.preventDefault();
        $(this).ekkoLightbox({
            alwaysShowClose: true
        });
    });
})