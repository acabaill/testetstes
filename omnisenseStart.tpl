<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function() {
  omnisense = new Omnisense("{$key_api}", "{$id}");
omnisense.start();
$(".logout").on("click",function (e) {
  $.roc("trid", { path: "/" });
});
$(document).on("click", ".ajax_add_to_cart_button", function(e)  {
    var id = $(this).data('id-product');
    $.ajax({
        url: baseDir + 'modules/Omnisense/ajax.php',
        type: "GET",
        data: { 'product_id': id },
        dataType: 'json',
        success:function(data)
        {
            omnisense.trackEvent("product.cart", data);
        },
  });
    });
}, false);
</script>
