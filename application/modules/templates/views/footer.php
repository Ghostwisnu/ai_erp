     <!--footer start-->
     <footer class="site-footer">
         <div class="text-center">
             2018 &copy; FlatLab by VectorLab.
             <a href="#" class="go-top">
                 <i class="fa fa-angle-up"></i>
             </a>
         </div>
     </footer>
     <!--footer end-->
     </section>

     <!-- Make sure jQuery is loaded before other scripts -->
     <script src="<?= base_url('assets/'); ?>js/jquery.js"></script>
     <script src="<?= base_url('assets/'); ?>js/bootstrap.bundle.min.js"></script>
     <script src="<?= base_url('assets/'); ?>js/jquery.dcjqaccordion.2.7.js"></script>
     <script src="<?= base_url('assets/'); ?>js/jquery.scrollTo.min.js"></script>
     <script src="<?= base_url('assets/'); ?>js/jquery.nicescroll.js"></script>
     <script src="<?= base_url('assets/'); ?>js/jquery.sparkline.js"></script>
     <script src="<?= base_url('assets/'); ?>assets/jquery-easy-pie-chart/jquery.easy-pie-chart.js"></script>
     <script src="<?= base_url('assets/'); ?>js/owl.carousel.js"></script>
     <script src="<?= base_url('assets/'); ?>js/jquery.customSelect.min.js"></script>
     <script src="<?= base_url('assets/'); ?>js/respond.min.js"></script>

     <!-- Right slidebar script -->
     <script src="<?= base_url('assets/'); ?>js/slidebars.min.js"></script>

     <!-- Common script for all pages -->
     <script src="<?= base_url('assets/'); ?>js/common-scripts.js"></script>

     <!-- Scripts for this page -->
     <script src="<?= base_url('assets/'); ?>js/sparkline-chart.js"></script>
     <script src="<?= base_url('assets/'); ?>js/easy-pie-chart.js"></script>
     <script src="<?= base_url('assets/'); ?>js/count.js"></script>


     <script>
         //owl carousel

         $(document).ready(function() {
             $("#owl-demo").owlCarousel({
                 navigation: true,
                 slideSpeed: 300,
                 paginationSpeed: 400,
                 singleItem: true,
                 autoPlay: true

             });
         });

         //custom select box

         $(function() {
             $('select.styled').customSelect();
         });

         $(window).on("resize", function() {
             var owl = $("#owl-demo").data("owlCarousel");
             owl.reinit();
         });
     </script>

     </body>

     <!-- Mirrored from thevectorlab.net/flatlab-4/index.html by HTTrack Website Copier/3.x [XR&CO'2014], Tue, 09 Sep 2025 16:28:58 GMT -->

     </html>