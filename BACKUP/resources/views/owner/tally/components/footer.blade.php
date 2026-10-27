 
 <!-- Password Toggle Script -->
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const toggle = document.querySelector(".toggle-password");

        toggle.addEventListener("click", function () {
            const input = document.querySelector(this.getAttribute("toggle"));

            if (input.type === "password") {
                input.type = "text";
                this.classList.remove("fa-eye");
                this.classList.add("fa-eye-slash");
            } else {
                input.type = "password";
                this.classList.remove("fa-eye-slash");
                this.classList.add("fa-eye");
            }
        });
    });
</script>

    
    <!-- Required vendors -->
    <script src="{{ asset('asset/vendor/global/global.min.js') }}"></script>


	<!-- counter -->
	<script src="{{ asset('asset/vendor/counter/counter.min.js') }}"></script>
	<script src="{{ asset('asset/vendor/counter/waypoint.min.js') }}"></script>
	
	<!-- Apex Chart -->
	<script src="{{ asset('asset/vendor/apexchart/apexchart.js') }}"></script>
	<script src="{{ asset('asset/vendor/chart-js/chart.bundle.min.js') }}"></script>
	<!-- Chart piety plugin files -->
    <script src="{{ asset('asset/vendor/peity/jquery.peity.min.js') }}"></script>
	<!-- Dashboard 1 -->
	<script src="{{ asset('asset/js/dashboard/dashboard-1.js') }}"></script>
	
	<script src="{{ asset('asset/vendor/owl-carousel/owl.carousel.js') }}"></script>

	<script src="{{ asset('asset/vendor/datatables/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('asset/vendor/datatables/responsive/responsive.js') }}"></script>
    <script src="{{ asset('asset/js/plugins-init/datatables.init.js') }}"></script>
	
    {{-- <script src="{{ asset('asset/js/styleSwitcher.js') }}"></script> --}}
	<script>
		function cardsCenter()
		{
			/*  testimonial one function by = owl.carousel.js */
			jQuery('.card-slider').owlCarousel({
				loop:true,
				margin:0,
				nav:true,
				//center:true,
				slideSpeed: 3000,
				paginationSpeed: 3000,
				dots: true,
				navText: ['<i class="fas fa-arrow-left"></i>', '<i class="fas fa-arrow-right"></i>'],
				responsive:{
					0:{
						items:1
					},
					576:{
						items:1
					},	
					800:{
						items:1
					},			
					991:{
						items:1
					},
					1200:{
						items:1
					},
					1600:{
						items:1
					}
				}
			})
		}
		
		jQuery(window).on('load',function(){
			setTimeout(function(){
				cardsCenter();
			}, 1000); 
		});
	</script>


	<script src="{{ asset('asset/vendor/bootstrap-select/js/bootstrap-select.min.js') }}"></script>
    <script src="{{ asset('asset/js/custom.min.js') }}"></script>
    <script src="{{ asset('asset/js/dlabnav-init.js') }}"></script>
    <script src="{{ asset('asset/js/demo.js') }}"></script>
</body>
</html>