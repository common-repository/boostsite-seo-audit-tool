/**
 * Plugin Template admin js.
 *
 *  @package WordPress Plugin Template/JS
 */

jQuery(document).ready(
    function ($) {
        //     var limit = 1;
        //     $('.checkbox-weekday input').on('change', function (evt) {
        //         if ($(this).siblings(':checked').length >= limit) {
        //             this.checked = false;
        //         }
        //     });
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
        const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
    }



);
