let event_classes = {
    "" : 'event-red',
    "1": 'event-yellow',
    "2": 'event-green',
    "3": 'event-orange',
    "4": 'event-blue',
    "5": 'event-beige',
    "6": 'event-red',
};

jQuery(document).ready(function (){
    jQuery(".buffet-calendar-select").change(function (){
        let id = jQuery(this).data('id');
        jQuery("."+id).removeClass("event-green event-blue event-yellow event-red event-orange event-beige")
            .addClass(event_classes[jQuery(this).val()])
        ;
    });
})