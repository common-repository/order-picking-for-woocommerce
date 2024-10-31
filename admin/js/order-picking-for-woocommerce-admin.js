var opfw_loader =
  '<svg class="opfw_loader" width="38" height="38" viewBox="0 0 38 38" xmlns="http://www.w3.org/2000/svg" stroke="#000"> <g fill="none" fill-rule="evenodd"><g transform="translate(1 1)" stroke-width="2"><circle stroke-opacity=".5" cx="18" cy="18" r="18"/> <path d="M36 18c0-9.94-8.06-18-18-18"><animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="1s" repeatCount="indefinite"/></path> </g></g></svg>';

jQuery(document).ready(function ($) {
  "use strict";

  
  $("body").on("click", ".opfw_print_button", function (event) {
      event.preventDefault();
      var printContents = $(".opfw_print_section").html();
    
      var WinPrint = window.open('', '', 'left=0,top=0,width=800,height=900,toolbar=0,scrollbars=0,status=0');
      WinPrint.document.write('<html><head><title>'+ $(".opfw_print_section h1").text() +'</title></head><body class="opfw_print_body">');
      WinPrint.document.write(printContents);
      WinPrint.document.write('</body></html>');
      WinPrint.document.close();
      WinPrint.focus();
      WinPrint.print();
      return false;
  });

  $("body").on("click", ".opfw_premium_close", function () {
    $(this).parent().hide();
    return false;
  });
  $("body").on("click", ".opfw_star_button", function () {
    if ($(this).next().is(":visible")) {
      $(this).next().hide();
    } else {
      $(".opfw_premium_feature_note").hide();
      $(this).next().show();
    }
    return false;
  });

  //$("#opfw_picking_statuses_select").selectWoo();

  if ($("#awaiting_fulfillment_screen").length) {
    $("#opfw_loading_div").show();
    $("#opfw_loading_div .opfw_loader_icon").html(opfw_loader);
    load_awaiting_fulfillment_screen();
    setInterval(function () {
      load_awaiting_fulfillment_screen();
    }, 30 * 1000);
  }

   


  function load_awaiting_fulfillment_screen() {
    var lastScreen = localStorage.getItem('opfw_lastScreen');
    var $opfwContent = $("#opfw_picking_orders_wrap .opfw_content");
  
    if ($opfwContent.is(':empty')) {

      if (lastScreen === "products") {
        load_awaiting_fulfillment_products();
      } 
      else if (lastScreen === "category") { 
        load_fulfillment_by_category();
      } 
      else {
        load_awaiting_fulfillment_orders();
      }
   
    } else {
      
      // Show the current screen
      if ( $(".opfw_order.opfw_product").length > 0 )
      {
        load_awaiting_fulfillment_products(); 
      } else if ( $(".opfw_order.opfw_category").length > 0 ){
        load_fulfillment_by_category();
      } else {
        load_awaiting_fulfillment_orders();
      }
 

    }
  }



  $("body").on("click", "#load_fulfillment_by_order", function () {
    $("#opfw_loading_div").show();
    $("#opfw_loading_div .opfw_loader_icon").html(opfw_loader);
    $("#opfw_picking_orders_wrap .opfw_content").html("");
    load_awaiting_fulfillment_orders();
    return false;
  });


   

  $("body").on("click", "#load_fulfillment_by_category", function () {
    $("#opfw_loading_div").show();
    $("#opfw_loading_div .opfw_loader_icon").html(opfw_loader);
    $("#opfw_picking_orders_wrap .opfw_content").html("");
    load_fulfillment_by_category();
    return false;
  });


  function load_fulfillment_by_category() {
    $(".opfw_screen_links").removeClass("current button-primary");
    $("#load_fulfillment_by_category").addClass("current button-primary");
   
    localStorage.setItem('opfw_lastScreen', 'category');
    $.ajax({
      type: "POST",
      url: opfw_ajax.ajaxurl,
      data: {
        action: "opfw_ajax",
        opfw_service: "get_awaiting_fulfillment_categories",
        opfw_wpnonce: opfw_nonce.nonce,
      },
      success: function (data) {
        $("#opfw_loading_div").hide();

        if (data.indexOf("opfw_order") >= 0) {
          
            var date_orders = $(data).filter(".opfw_order");
            date_orders.each(function () {
              var opfw_product_key = "#opfw_category_" + $(this).attr("data-category-key");
              if (!$(opfw_product_key).length) {
                $("#opfw_picking_orders_wrap .opfw_content").append($(this));
              }
            });
  
            $("#awaiting_fulfillment_screen img.lazyload").each(function () {
              var $opfw_src = jQuery(this).attr("data-src");
              jQuery(this).attr("src", $opfw_src);
            });

        } else {
           
          $("#opfw_picking_orders_wrap .opfw_content").html(data);
        }

        calculate_quantity_and_collected();

      },
      error: function (request, status, error) {},
    });
  }

  
  $("body").on("click", "#load_fulfillment_by_product", function () {
    $("#opfw_loading_div").show();
    $("#opfw_loading_div .opfw_loader_icon").html(opfw_loader);
    $("#opfw_picking_orders_wrap .opfw_content").html("");
    load_awaiting_fulfillment_products();
    return false;
  });


  function load_awaiting_fulfillment_products() {
    

    $(".opfw_screen_links").removeClass("current button-primary");
    $("#load_fulfillment_by_product").addClass("current button-primary");
    
    localStorage.setItem('opfw_lastScreen', 'products');
    $.ajax({
      type: "POST",
      url: opfw_ajax.ajaxurl,
      data: {
        action: "opfw_ajax",
        opfw_service: "get_awaiting_fulfillment_products",
        opfw_wpnonce: opfw_nonce.nonce,
      },
      success: function (data) {
        $("#opfw_loading_div").hide();

        if (data.indexOf("opfw_order") >= 0) {
          
            var date_orders = $(data).filter(".opfw_order");
            date_orders.each(function () {
              var opfw_product_key = "#opfw_product_" + $(this).attr("data-product-key");
              if (!$(opfw_product_key).length) {
                $("#opfw_picking_orders_wrap .opfw_content").append($(this));
              }
            });
  
            $("#awaiting_fulfillment_screen img.lazyload").each(function () {
              var $opfw_src = jQuery(this).attr("data-src");
              jQuery(this).attr("src", $opfw_src);
            });

        } else {
           
          $("#opfw_picking_orders_wrap .opfw_content").html(data);
        }

        calculate_quantity_and_collected();

      },
      error: function (request, status, error) {},
    });
  }

  function calculate_quantity_and_collected(){
     
    $(".opfw_content > .opfw_order").each(function(){
      var current_quantity = 0 ;
      var current_collected = 0
      $(this).find('.opfw_products .opfw_product_box').each(function(){
        var productQuantity = parseInt($(this).attr('data-quantity'), 10);
        var productCollected = parseInt($(this).attr('data-collected'), 10);
         
        current_quantity += productQuantity;
        current_collected+= productCollected;
      });
      $(this).find(".opfw_product_quantity span").html(current_quantity);
      $(this).find(".opfw_product_collected span").html(current_collected);
     
    });
  }

  function load_awaiting_fulfillment_orders() {
    
    $(".opfw_screen_links").removeClass("current button-primary");
    $("#load_fulfillment_by_order").addClass("current button-primary");

    localStorage.setItem('opfw_lastScreen', 'orders');
    $.ajax({
      type: "POST",
      url: opfw_ajax.ajaxurl,
      data: {
        action: "opfw_ajax",
        opfw_service: "get_awaiting_fulfillment_orders",
        opfw_wpnonce: opfw_nonce.nonce,
      },
      success: function (data) {
        $("#opfw_loading_div").hide();

        if (data.indexOf("opfw_order") >= 0) {
          var date_orders = $(data).filter(".opfw_order");
          date_orders.each(function () {
            var opfw_order_id = "#opfw_order_" + $(this).attr("data-order-id");
            if (!$(opfw_order_id).length) {
              $("#opfw_picking_orders_wrap .opfw_content").append($(this));
            }
          });

          opfw_set_picking_order_button();

          $("#awaiting_fulfillment_screen img.lazyload").each(function () {
            var $opfw_src = jQuery(this).attr("data-src");
            jQuery(this).attr("src", $opfw_src);
          });
        } else {
          $("#opfw_picking_orders_wrap .opfw_content").html(data);
        }
        calculate_quantity_and_collected();
      },
      error: function (request, status, error) {},
    });
  }

  function opfw_set_picking_order_button() {
    $("#opfw_picking_orders_wrap .opfw_order").each(function () {
      var opfw_order_id = $(this).attr("data-order-id");
      var opfw_order_status = "awaiting_fulfillment";
      var opfw_produt_fulfilled = $(this).find(
        ".opfw_picking_status_fulfilled"
      ).length;
      var opfw_produt_partially_fulfilled = $(this).find(
        ".opfw_picking_status_partially_fulfilled"
      ).length;
      var opfw_produt_unfulfilled = $(this).find(
        ".opfw_picking_status_unfulfillment"
      ).length;

      // Set order picking status.
      if (
        opfw_produt_unfulfilled > 0 &&
        opfw_produt_partially_fulfilled == 0 &&
        opfw_produt_partially_fulfilled == 0
      ) {
        opfw_order_status = "unfulfillment";
      }

      if (
        opfw_produt_partially_fulfilled > 0 ||
        (opfw_produt_fulfilled > 0 && opfw_produt_unfulfilled > 0)
      ) {
        opfw_order_status = "partially_fulfilled";
      }

      if (
        opfw_produt_fulfilled > 0 &&
        opfw_produt_unfulfilled == 0 &&
        opfw_produt_partially_fulfilled == 0
      ) {
        opfw_order_status = "fulfilled";
      }

      var opfw_order_button = $(this).find(
        ".opfw_order_button .opfw_order_set_fulfillment"
      );

      opfw_order_button.attr("data-status", opfw_order_status);
      opfw_order_button
        .find(".opfw_label")
        .html(opfw_statuses[opfw_order_status]);
    });
  }

  function opfw_load_picking_orders_screen(opfw_orders) {
    let opfw_loading_div = $("#opfw_loading_div").html();

    if ($("#opfw_picking_orders_wrap").length == false) {
      $("body").append(
        '<div class="opfw_overlay opfw_lightbox" id="opfw_picking_orders_wrap" style="display:none"><a href="#" class="opfw_lightbox_close">×</a><div class="opfw_inner"><div class="opfw_content"></div></div></div>'
      );
    }
    $("#opfw_picking_orders_wrap .opfw_content").html(opfw_loading_div);
    $("#opfw_picking_orders_wrap .opfw_content .opfw_loader_icon").html(
      opfw_loader
    );

    $("#opfw_picking_orders_wrap").show();

    $.ajax({
      type: "POST",
      url: opfw_ajax.ajaxurl,
      data: {
        action: "opfw_ajax",
        opfw_service: "get_orders",
        opfw_wpnonce: opfw_nonce.nonce,
        opfw_order_id: opfw_orders,
      },
      success: function (data) {
        $("#opfw_picking_orders_wrap .opfw_inner .opfw_content").html(data);
        opfw_set_picking_order_button();

        $("#opfw_picking_orders_wrap img.lazyload").each(function () {
          var $opfw_src = jQuery(this).attr("data-src");
          jQuery(this).attr("src", $opfw_src);
        });
      },
      error: function (request, status, error) {},
    });
  }

  // Show badge on admin panel order page.
  if ($("#opfw_admin_order_page_badge").length) {
    $("#opfw_admin_order_page_badge").insertAfter(
      "#order_data .woocommerce-order-data__heading"
    );
    $("#opfw_admin_order_page_badge").show();
  }

  $("body").on("click", "#opfw_picking_order", function () {
    var opfw_order_id = $(this).attr("data-order");
    opfw_load_picking_orders_screen(opfw_order_id);
    return false;
  });

  $("body").on("click", ".opfw_order_picking_action", function () {
    var opfw_order_id = $(this).parents("tr").attr("id");
    opfw_order_id = opfw_order_id.replace("post-", "");
    opfw_load_picking_orders_screen(opfw_order_id);
    return false;
  });

  $("body").on("click", "#opfw_full_screen", function () {
    $("#awaiting_fulfillment_screen #opfw_picking_orders_wrap").addClass(
      "opfw_overlay opfw_lightbox"
    );
    $("html").addClass("with-opfw-opacity");
    $("#opfw_full_screen").hide();
    $("#opfw_minimize_screen").show();
    $("#opfw_picking_orders_wrap").show();
    return false;
  });

  $("body").on("click", "#opfw_minimize_screen", function () {
    $("html").removeClass("with-opfw-opacity");
    $("#awaiting_fulfillment_screen  #opfw_picking_orders_wrap").removeClass(
      "opfw_overlay opfw_lightbox"
    );
    $("#opfw_minimize_screen").hide();
    $("#opfw_full_screen").show();
    return false;
  });

  $("body").on(
    "click",
    "#opfw_picking_orders_wrap > .opfw_lightbox_close",
    function () {
      $(this).parent().hide();
      $("html").removeClass("with-opfw-opacity");
      $("#opfw_picking_orders_wrap .opfw_inner .opfw_content").html("");

      return false;
    }
  );

  $("body").on(
    "click",
    ".opfw_product_lightbox .opfw_lightbox_close",
    function () {
      $(this).parent().parent().hide();
      $("html").removeClass("with-opfw-opacity");
      return false;
    }
  );

  $("body").on("click", ".opfw_info_icon", function () {
    $(".opfw_product_lightbox").hide();
    var opfw_product_description = $(this)
      .parents(".opfw_product_box")
      .find(".opfw_product_lightbox");
    if (opfw_product_description.is(":hidden")) {
      opfw_product_description.show();
    } else {
      opfw_product_description.hide();
    }
    return false;
  });

  $("body").on("click", ".opfw_product_overlay a", function () {
    var opfw_product_box = $(this).parents(".opfw_product_box");
    $(this).hide();
    opfw_product_box.attr("class", "opfw_product_box");
    opfw_product_box.find(".opfw_undo_icon").show();
    opfw_show_product_box_form(opfw_product_box, "hide");
    opfw_set_picking_order_button();
    return false;
  });

  function opfw_hide_order(opfw_order) {
    $(opfw_order).fadeOut("slow", function () {
      $(this).replaceWith("");
      if ($(".opfw_order").length == 0) {
        $(".opfw_overlay").hide();
        $("#opfw_minimize_screen").trigger("click");
        /*
        $("html").removeClass("with-opfw-opacity");
        $("#awaiting_fulfillment_screen #opfw_picking_orders_wrap").removeClass(
          "opfw_overlay opfw_lightbox"
        );
        */
      }
    });
  }

  function opfw_switch_product_box_class(obj, opfw_status) {
    var opfw_product_box = $(obj).parents(".opfw_product_box");
    opfw_product_box.attr("class", "opfw_product_box picking_done");
    opfw_product_box.addClass("opfw_picking_status_" + opfw_status);
    opfw_product_box.find(".opfw_undo_icon").attr("data-status", opfw_status);
    opfw_set_picking_order_button();
  }

  function opfw_set_picking(obj, status) {
    var opfw_order_id = $(obj).parents(".opfw_order").attr("data-order-id");
    var opfw_product_box = $(obj).parents(".opfw_product_box");
    var opfw_product_id = opfw_product_box.attr("data-product-id");
    var opfw_status = $(obj).attr("data-status");
    var opfw_quantity = opfw_product_box.find(".opfw_quantity").val();
    var opfw_note = opfw_product_box.find(".opfw_note").val();

    if (opfw_status == "unfulfillment" && opfw_quantity > 0) {
      opfw_status = "partially_fulfilled";
    }
    if (opfw_status == "fulfilled") {
      opfw_quantity = opfw_product_box.attr("data-quantity");
    }

    opfw_switch_product_box_class(obj, opfw_status);
    opfw_set_picking_order_button();
    $.ajax({
      type: "POST",
      url: opfw_ajax.ajaxurl,
      data: {
        action: "opfw_ajax",
        opfw_service: "set_picking",
        opfw_wpnonce: opfw_nonce.nonce,
        opfw_status: opfw_status,
        opfw_order_id: opfw_order_id,
        opfw_product_id: opfw_product_id,
        opfw_note: opfw_note,
        opfw_quantity: opfw_quantity,
      },
      success: function (data) {
        var opfw_json = JSON.parse(data);

        opfw_product_box
          .find(".opfw_picking_note .opfw_quantity")
          .html(opfw_json["quantity"]);

          opfw_product_box
          .attr("data-collected", opfw_json["quantity"]);
           

        opfw_product_box
          .find(".opfw_picking_note .opfw_user")
          .html(opfw_json["user"]);
        opfw_product_box
          .find(".opfw_picking_note .opfw_date")
          .html(opfw_json["date"]);
        var opfw_picking_note = "";
        if (opfw_json["note"] != "") {
          opfw_picking_note = "<span>" + opfw_json["note"] + "</span>";
        }
        opfw_product_box
          .find(".opfw_picking_note .opfw_note_text")
          .html(opfw_picking_note);

          calculate_quantity_and_collected();


      },
      error: function (request, status, error) {},
    });
    return false;
  }

  $("body").on("click", ".opfw_picked_icon", function () {
    opfw_set_picking(this);
    return false;
  });

  function opfw_show_product_box_form(opfw_product_box, action) {
    if (action == "show") {
      opfw_product_box.find(".opfw_product_form").show();
      opfw_product_box.find(".opfw_item_name").hide();
      opfw_product_box.find(".opfw_item_sku").hide();
      opfw_product_box.find(".opfw_item_quantity").hide();
      opfw_product_box.find(".opfw_item_variation").hide();
      opfw_product_box.find(".opfw_product_icons").hide();
      
    } else {
      opfw_product_box.find(".opfw_product_form").hide();
      opfw_product_box.find(".opfw_item_name").show();
      
      opfw_product_box.find(".opfw_item_sku").show();
      opfw_product_box.find(".opfw_item_quantity").show();
      opfw_product_box.find(".opfw_item_variation").show();
      opfw_product_box.find(".opfw_product_icons").css("display", "flex");
    }
     
  }

  function opfw_handle_product_form(obj) {
    var opfw_product_box = $(obj).parents(".opfw_product_box");

    if (opfw_product_box.find(".opfw_product_form").is(":hidden")) {
      opfw_show_product_box_form(opfw_product_box, "show");
    } else {
      opfw_show_product_box_form(opfw_product_box, "hide");
    }

    return false;
  }

  $("body").on("click", ".opfw_product_form .cancel", function () {
    opfw_handle_product_form(this);
    return false;
  });

  $("body").on("click", ".opfw_product_form .send", function () {
    opfw_handle_product_form(this);
    opfw_set_picking(this);
  
    return false;
  });

  $("body").on("click", ".opfw_unpicked_icon", function () {
    opfw_handle_product_form(this);
    return false;
  });

  $("body").on("click", ".opfw_undo_icon", function () {
    var opfw_product_box = $(this).parents(".opfw_product_box");
    var opfw_status = $(this).attr("data-status");
    opfw_switch_product_box_class(this, opfw_status);
    opfw_show_product_box_form(opfw_product_box, "hide");
    $(this).hide();
    return false;
  });

  $("body").on(
    "click",
    ".opfw_order_button .opfw_order_cancel_fulfillment",
    function () {
      var opfw_order = $(this).parents(".opfw_order");
      var opfw_order_id = opfw_order.attr("data-order-id");

      $(this)
        .parents(".opfw_order")
        .find(".opfw_product_box")
        .each(function () {
          $(this).attr("class", "opfw_product_box");
        });

      $.ajax({
        type: "POST",
        url: opfw_ajax.ajaxurl,
        data: {
          action: "opfw_ajax",
          opfw_service: "cancel_order_picking",
          opfw_wpnonce: opfw_nonce.nonce,
          opfw_order_id: opfw_order_id,
        },
        success: function (data) {},
        error: function (request, status, error) {},
      });
      opfw_hide_order(opfw_order);

      // Update badges.
      $(".opfw_badge_order_" + opfw_order_id).each(function () {
        $(this).hide();
        $(this).html("");
        $(this).removeClass("opfw_badge_" + $(this).attr("data-status"));
        $(this).attr("data-status", "opfw_canceled");
      });

      return false;
    }
  );

  $("body").on(
    "click",
    ".opfw_order_button .opfw_products_set_collected",
    function () {
      var opfw_orders = $(this).parents(".opfw_order");
      opfw_orders.find(".opfw_product_box .opfw_picked_icon").each(function () {
        $(this).trigger("click");
      });
    });

  $("body").on(
    "click",
    ".opfw_order_button .opfw_order_set_fulfillment",
    function () {
      var opfw_order = $(this).parents(".opfw_order");
      var opfw_order_id = opfw_order.attr("data-order-id");
      var opfw_order_status = $(this).attr("data-status");

      if (opfw_order_status != "awaiting_fulfillment") {
        // Mark products without picking status
        opfw_order.find(".opfw_product_box").each(function () {
          if (!$(this).hasClass("picking_done")) {
            $(this).find(".opfw_quantity").val("0");
            $(this).find(".opfw_note").val("");
            $(this).find(".opfw_product_form .send").trigger("click");
          }
        });
      }

      $.ajax({
        type: "POST",
        url: opfw_ajax.ajaxurl,
        data: {
          action: "opfw_ajax",
          opfw_service: "set_order_picking",
          opfw_wpnonce: opfw_nonce.nonce,
          opfw_status: opfw_order_status,
          opfw_order_id: opfw_order_id,
        },
        success: function (data) {},
        error: function (request, status, error) {},
      });

      // Update badges.
      $(".opfw_badge_order_" + opfw_order_id).each(function () {
        $(this).show();
        $(this).html("<span>" + opfw_statuses[opfw_order_status] + "</span>");
        $(this).removeClass("opfw_badge_" + $(this).attr("data-status"));
        $(this).addClass("opfw_badge_" + opfw_order_status);
        $(this).attr("data-status", opfw_order_status);
      });

      opfw_hide_order(opfw_order);
      return false;
    }
  );

  $("body").on("click", "#opfw_picking_orders", function () {
    $("html").addClass("with-opfw-opacity");

    let opfw_alert = $(this).attr("data-alert");
    let opfw_order_attr = $(this).attr("data-order");
    let opfw_orders = "";

    $("input[name='" + opfw_order_attr + "']").each(function () {
      if ($(this).is(":checked")) {
        if (opfw_orders != "") {
          opfw_orders = opfw_orders + ",";
        }
        opfw_orders = opfw_orders + $(this).val();
      }
    });

    if (opfw_orders == "") {
      alert(opfw_alert);
      return false;
    }
    opfw_load_picking_orders_screen(opfw_orders);
  });
});
