<!-- ========================================================
     فوتر (تذييل) الموقع — يظهر في كل صفحات الواجهة العامة
     ======================================================== -->
<footer class="main-footer">
  <div class="container footer-grid">

    <!-- عمود: عن الفندق + أيقونات التواصل الاجتماعي -->
    <div class="footer-col">
      <h3><i class="fa-solid fa-hotel"></i> الصرح الذهبي</h3>
      <p>فندق خمس نجوم يجمع بين الفخامة الأصيلة والضيافة الراقية، في قلب المدينة.</p>

      
      <div class="socials">
        <a href="#" target="_blank" title="فيسبوك">
          <img src="assets/img/social/facebook.png" alt="فيسبوك">
        </a>
        <a href="#" target="_blank" title="انستغرام">
          <img src="assets/img/social/instagram.png" alt="انستغرام">
        </a>
        <a href="#" target="_blank" title="تويتر">
          <img src="assets/img/social/twitter.png" alt="تويتر">
        </a>
        <a href="https://wa.me/967785059967" target="_blank" title="واتساب">
          <img src="assets/img/social/whatsapp.png" alt="واتساب">
        </a>
      </div>
    </div>

    <!-- عمود: روابط سريعة لصفحات الموقع -->
    <div class="footer-col">
      <h4>روابط سريعة</h4>
      <a href="rooms.php">الغرف والأجنحة</a>
      <a href="services.php">الخدمات</a>
      <a href="gallery.php">معرض الصور</a>
      <a href="about.php">من نحن</a>
    </div>

    <!-- عمود: بيانات التواصل (العنوان وأرقام الهاتف والبريد) -->
    <div class="footer-col">
      <h4>تواصل معنا</h4>
      <p><i class="fa-solid fa-location-dot"></i> شارع الخمسين، بجوار جسر بيت بوس</p>
      <p><i class="fa-solid fa-phone"></i> 785059967</p>
      <p><i class="fa-solid fa-phone"></i> 736146222</p>
      <p><i class="fa-solid fa-envelope"></i> info@alsarh.com</p>
    </div>

  </div>

  <div class="footer-bottom">
    <?php
      // date('Y'): دالة PHP جاهزة تُرجع السنة الحالية فقط (مثل 2026)
      // نستخدمها هنا حتى تتحدث سنة حقوق النشر تلقائيًا كل عام دون تعديل الكود يدويًا.
    ?>
    &copy; <?php echo date('Y'); ?> فندق الصرح الذهبي - جميع الحقوق محفوظة
  </div>
</footer>

<script src="assets/js/main.js"></script>
</body>
</html>
