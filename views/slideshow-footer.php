   		</div>
	</div>
</div>


 <script type="text/javascript">
        jQuery('#carousel-example-generic').on("slide.bs.carousel", function (event) {
    		//
        });

        jQuery('.videowrapper iframe').iframeTracker({
            blurCallback: function(){
                jQuery('#carousel-example-generic').carousel('pause');
            }
        });

</script>