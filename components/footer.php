<footer class="site-footer" role="contentinfo">
    <!-- Bubble Animation -->
    <div class="footer-bubbles">
        <div class="footer-bubble"></div>
        <div class="footer-bubble"></div>
        <div class="footer-bubble"></div>
        <div class="footer-bubble"></div>
    </div>

    <!-- Back to Top -->
    <a href="#top" class="footer-back-to-top" aria-label="Back to top">
        <i class="fas fa-chevron-up"></i>
    </a>

    <div class="footer-container">
        <!-- Footer Top -->
        <div class="footer-top">
            <!-- Brand Section -->
            <div class="footer-brand">
                <a href="/" class="footer-logo">
                    <div class="footer-logo-icon">
                        <i class="fas fa-bubbles"></i>
                    </div>
                    <div class="footer-logo-text">Click n' Pop</div>
                </a>
                
                <p class="footer-tagline">
                    The most addictive bubble popping experience. Challenge your reflexes, 
                    compete with friends, and pop your way to the top!
                </p>
                
                <div class="footer-social">
                    <a href="https://twitter.com/clicknpop" class="social-link" target="_blank" aria-label="Twitter">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="https://discord.gg/clicknpop" class="social-link" target="_blank" aria-label="Discord">
                        <i class="fab fa-discord"></i>
                    </a>
                    <a href="https://instagram.com/clicknpop" class="social-link" target="_blank" aria-label="Instagram">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="https://youtube.com/clicknpop" class="social-link" target="_blank" aria-label="YouTube">
                        <i class="fab fa-youtube"></i>
                    </a>
                    <a href="https://tiktok.com/@clicknpop" class="social-link" target="_blank" aria-label="TikTok">
                        <i class="fab fa-tiktok"></i>
                    </a>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="footer-links-group">
                <h3 class="footer-heading">
                    <i class="fas fa-gamepad"></i>
                    Game
                </h3>
                <ul class="footer-links">
                    <li><a href="/play"><i class="fas fa-chevron-right"></i> Play Now</a></li>
                    <li><a href="/leaderboard"><i class="fas fa-chevron-right"></i> Leaderboard</a></li>
                    <li><a href="/tournaments"><i class="fas fa-chevron-right"></i> Tournaments</a></li>
                    <li><a href="/challenges"><i class="fas fa-chevron-right"></i> Daily Challenges</a></li>
                    <li><a href="/achievements"><i class="fas fa-chevron-right"></i> Achievements</a></li>
                </ul>
            </div>

            <!-- Community -->
            <div class="footer-links-group">
                <h3 class="footer-heading">
                    <i class="fas fa-users"></i>
                    Community
                </h3>
                <ul class="footer-links">
                    <li><a href="/forum"><i class="fas fa-chevron-right"></i> Forums</a></li>
                    <li><a href="/blog"><i class="fas fa-chevron-right"></i> Blog</a></li>
                    <li><a href="/guides"><i class="fas fa-chevron-right"></i> Guides</a></li>
                    <li><a href="/events"><i class="fas fa-chevron-right"></i> Events</a></li>
                    <li><a href="/partners"><i class="fas fa-chevron-right"></i> Partners</a></li>
                </ul>
            </div>

            <!-- Support -->
            <div class="footer-links-group">
                <h3 class="footer-heading">
                    <i class="fas fa-headset"></i>
                    Support
                </h3>
                <ul class="footer-links">
                    <li><a href="/help"><i class="fas fa-chevron-right"></i> Help Center</a></li>
                    <li><a href="/faq"><i class="fas fa-chevron-right"></i> FAQ</a></li>
                    <li><a href="/contact"><i class="fas fa-chevron-right"></i> Contact Us</a></li>
                    <li><a href="/privacy"><i class="fas fa-chevron-right"></i> Privacy Policy</a></li>
                    <li><a href="/terms"><i class="fas fa-chevron-right"></i> Terms of Service</a></li>
                </ul>
            </div>

            <!-- Newsletter -->
            <div class="footer-newsletter">
                <h3 class="newsletter-title">Stay Updated!</h3>
                <p class="newsletter-text">
                    Subscribe to our newsletter for game updates, new features, and exclusive rewards.
                </p>
                <form class="newsletter-form">
                    <input type="email" class="newsletter-input" placeholder="Your email address" required>
                    <button type="submit" class="newsletter-button">
                        <i class="fas fa-paper-plane"></i>
                        Subscribe
                    </button>
                </form>
            </div>
        </div>

        <!-- Footer Bottom -->
        <div class="footer-bottom">
            <div class="copyright">
                &copy; <?= date('Y'); ?> Click n' Pop. All rights reserved.
                <a href="/sitemap">Sitemap</a> | 
                <a href="/accessibility">Accessibility</a>
            </div>
            
            <div class="footer-legal">
                <ul class="footer-legal-links">
                    <li><a href="/privacy">Privacy Policy</a></li>
                    <li><a href="/terms">Terms of Service</a></li>
                    <li><a href="/cookies">Cookie Policy</a></li>
                    <li><a href="/legal">Legal</a></li>
                </ul>
                
                <div class="payment-methods">
                    <span class="payment-text">Secure payments:</span>
                    <div class="payment-icons">
                        <div class="payment-icon" title="Visa">
                            <i class="fab fa-cc-visa"></i>
                        </div>
                        <div class="payment-icon" title="Mastercard">
                            <i class="fab fa-cc-mastercard"></i>
                        </div>
                        <div class="payment-icon" title="PayPal">
                            <i class="fab fa-cc-paypal"></i>
                        </div>
                        <div class="payment-icon" title="Stripe">
                            <i class="fab fa-cc-stripe"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>