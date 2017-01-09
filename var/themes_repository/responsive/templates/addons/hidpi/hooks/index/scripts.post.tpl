{* Modified to fix bug #006201 by tommy from cs-cart.jp 2016 *}
{* See : http://forum.cs-cart.com/tracker/issue-6201-hidpi-scroller-and-thumbnail-bugs-in-435/?verfilter=125 *}

{script src="js/addons/hidpi/retina.js"}
<script type="text/javascript">
    Retina.configure({
        image_host: '{$hidpi_image_host|escape:javascript}'
        image_host: '{$hidpi_image_host|escape:javascript}',
        check_mime_type: true,
            retinaImgTagSelector: 'img',
            retinaImgFilterFunc: undefined
    });
</script>
{script src="js/addons/hidpi/func.js"}