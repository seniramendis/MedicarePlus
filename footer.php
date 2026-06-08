<footer class="main-footer">
    <div class="container">
        <div class="footer-grid">

            <!-- Brand -->
            <div class="footer-brand">
                <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px">
                    <div class="logo-icon"><i class="fas fa-leaf"></i></div>
                    <span style="font-family:var(--font-display);font-size:1.25rem;color:#fff;font-weight:700">Medicare Plus</span>
                </div>
                <p>Sri Lanka's trusted digital health platform — connecting patients with specialist doctors across the island since 2022.</p>
                <div style="display:flex;gap:10px">
                    <a href="#" style="width:34px;height:34px;border-radius:8px;background:rgba(255,255,255,.1);display:flex;align-items:center;justify-content:center;color:rgba(255,255,255,.6);font-size:.85rem;transition:all .25s" onmouseover="this.style.background='var(--teal-light)';this.style.color='#fff'" onmouseout="this.style.background='rgba(255,255,255,.1)';this.style.color='rgba(255,255,255,.6)'"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" style="width:34px;height:34px;border-radius:8px;background:rgba(255,255,255,.1);display:flex;align-items:center;justify-content:center;color:rgba(255,255,255,.6);font-size:.85rem;transition:all .25s" onmouseover="this.style.background='var(--teal-light)';this.style.color='#fff'" onmouseout="this.style.background='rgba(255,255,255,.1)';this.style.color='rgba(255,255,255,.6)'"><i class="fab fa-twitter"></i></a>
                    <a href="#" style="width:34px;height:34px;border-radius:8px;background:rgba(255,255,255,.1);display:flex;align-items:center;justify-content:center;color:rgba(255,255,255,.6);font-size:.85rem;transition:all .25s" onmouseover="this.style.background='var(--teal-light)';this.style.color='#fff'" onmouseout="this.style.background='rgba(255,255,255,.1)';this.style.color='rgba(255,255,255,.6)'"><i class="fab fa-instagram"></i></a>
                </div>
            </div>

            <!-- Quick links -->
            <div class="footer-col">
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="Home.php">Home</a></li>
                    <li><a href="doctors.php">Find a Doctor</a></li>
                    <li><a href="services.php">Our Services</a></li>
                    <li><a href="book_appointment.php">Book Appointment</a></li>
                    <li><a href="blog.php">Health Blog</a></li>
                </ul>
            </div>

            <!-- Specialities -->
            <div class="footer-col">
                <h4>Specialities</h4>
                <ul>
                    <li><a href="doctors.php?spec=Cardiology">Cardiology</a></li>
                    <li><a href="doctors.php?spec=Neurology">Neurology</a></li>
                    <li><a href="doctors.php?spec=Paediatrics">Paediatrics</a></li>
                    <li><a href="doctors.php?spec=Gynaecology">Gynaecology</a></li>
                    <li><a href="doctors.php?spec=Dermatology">Dermatology</a></li>
                </ul>
            </div>

            <!-- Contact -->
            <div class="footer-col">
                <h4>Contact Us</h4>
                <div class="footer-contact-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <span>No. 47, Galle Road, Colombo 03, Sri Lanka</span>
                </div>
                <div class="footer-contact-item">
                    <i class="fas fa-phone-alt"></i>
                    <span>+94 11 234 5678</span>
                </div>
                <div class="footer-contact-item">
                    <i class="fas fa-envelope"></i>
                    <span>hello@medicareplus.lk</span>
                </div>
                <div class="footer-contact-item">
                    <i class="fas fa-clock"></i>
                    <span>Support: Mon–Fri, 8am–6pm</span>
                </div>
            </div>

        </div>

        <div class="footer-bottom">
            <span>&copy; <?= date('Y') ?> Medicare Plus (Pvt) Ltd. All rights reserved.</span>
            <div style="display:flex;gap:20px">
                <a href="#" style="color:rgba(255,255,255,.35);font-size:.8rem">Privacy Policy</a>
                <a href="#" style="color:rgba(255,255,255,.35);font-size:.8rem">Terms of Use</a>
                <a href="#" style="color:rgba(255,255,255,.35);font-size:.8rem">Cookie Policy</a>
            </div>
        </div>
    </div>
</footer>
</body>
</html>
