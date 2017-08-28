<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function() {
  omnisense = new Omnisense("{$key_api}", "{$id}");
omnisense.start();
$(".logout").on("click",function (e) {
  $.roc("trid", { path: "/" });
});
$(document).on("click", ".quick-view", function(e)  {
    var id = $(this).closest('.product-miniature').data("id-product");
    $.ajax({
        url: '../modules/Omnisense/ajax.php',
        type: "GET",
        data: { 'product_id': id },
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        success:function(data)
        {
            omnisense.trackEvent("product.view", data);
            $(document).on('click', "button.add-to-cart", function(e)  {
              var items = new Object();
              items['quantity'] = $("#quantity_wanted").val();
              var obj = data;
              items['total_price'] = Number(items['quantity'] * obj['price']).toFixed(2);
              var product = $.extend(data, items);
              omnisense.trackEvent("product.cart", data);
              $(document).off('click', "button.add-to-cart");
            });
        },
    });
      });
 }, false);
 </script>
