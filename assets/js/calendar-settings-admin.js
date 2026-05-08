jQuery(document).ready(function ($) {
    var $rows = $("#buffet-calendar-settings-rows");
    var $template = $("#buffet-calendar-row-template");
    var $addBtn = $("#buffet-calendar-add-row");

    if (!$rows.length || !$template.length) {
        return;
    }

    function nextId() {
        var max = 0;
        $rows.find("tr").each(function () {
            var id = parseInt($(this).attr("data-row-id"), 10);
            if (!isNaN(id) && id > max) {
                max = id;
            }
        });
        return max + 1;
    }

    function initColorPicker($scope) {
        $scope.find(".buffet-calendar-color-picker").each(function () {
            var $el = $(this);
            if ($el.closest(".wp-picker-container").length) {
                return;
            }
            $el.wpColorPicker();
        });
    }

    initColorPicker($rows);

    $addBtn.on("click", function (e) {
        e.preventDefault();
        var newId = nextId();
        var html = $template.html().replace(/__ID__/g, newId);
        var $row = $($.parseHTML(html));
        $rows.append($row);
        initColorPicker($row);
    });

    $rows.on("click", ".buffet-calendar-remove-row", function (e) {
        e.preventDefault();
        var $row = $(this).closest("tr");
        var $picker = $row.find(".wp-picker-container");
        if ($picker.length) {
            $picker.remove();
        }
        $row.remove();
    });
});
