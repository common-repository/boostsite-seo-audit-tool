window.ajaxLoad = () => {

    console.log('send')
    jQuery.ajax({
        type: "post",
        url: ajaxurl,
        data: { action: "my_user_vote" },
        success: function (response) {
            console.log(response);
            if (response) {
                console.log(jQuery(".table-container"));
                jQuery(".table-container").html(response)
            }
        },
        error: function (err) {
            console.log(err);
        }
    })
}