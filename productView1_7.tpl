<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function() {
    omnisense.trackEvent("product.view", {$productView|@json_encode nofilter});
    $(document).on('click', "button.add-to-cart", function(e)  {
      var items = new Object();
      items['quantity'] = $("#quantity_wanted").val();
      var obj = {$productCart|@json_encode nofilter};
      items['total_price'] = Number(items['quantity'] * obj['price']).toFixed(2);
      var product = $.extend({$productCart|@json_encode nofilter}, items);
      omnisense.trackEvent("product.cart", product);
    });
}, false);
</script>
