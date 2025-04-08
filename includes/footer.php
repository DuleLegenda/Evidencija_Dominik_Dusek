<footer class="site-footer">
    <div class="footer-content">
        <div class="footer-section">
            <h4>ESP DD V5</h4>
            <p>Sustav za upravljanje službenim putovanjima</p>
        </div>
        
        <div class="footer-section">
            <h4>Kontakt</h4>
            <ul class="footer-links">
                <li><i class="fas fa-envelope"></i> podrska@espdd.hr</li>
                <li><i class="fas fa-phone"></i> +385 1 1234 567</li>
                <li><i class="fas fa-map-marker-alt"></i> Zagrebačka ulica 123, Zagreb</li>
            </ul>
        </div>
        
        <div class="footer-section">
            <h4>Brzi linkovi</h4>
            <ul class="footer-links">
                <li><a href="dashboard.php"><i class="fas fa-home"></i> Početna</a></li>
                <li><a href="travel_detail.php"><i class="fas fa-plane"></i> Moja putovanja</a></li>
                <li><a href="settings.php"><i class="fas fa-cog"></i> Postavke</a></li>
            </ul>
        </div>
    </div>
    
    <div class="footer-bottom">
        <p>&copy; <?php echo date("Y"); ?> ESP DD V5. Sva prava pridržana.</p>
        <p class="version">Verzija 5.0</p>
    </div>
</footer>

<style>
    /* FOOTER STYLES */
    .site-footer {
        background-color: #1e3a8a;
        color: white;
        padding: 2rem 0 0;
        margin-top: 3rem;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    
    .footer-content {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 2rem;
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 2rem 2rem;
    }
    
    .footer-section h4 {
        color: #93c5fd;
        margin-bottom: 1rem;
        font-size: 1.1rem;
        font-weight: 600;
    }
    
    .footer-links {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .footer-links li {
        margin-bottom: 0.75rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .footer-links a {
        color: white;
        text-decoration: none;
        transition: color 0.3s ease;
    }
    
    .footer-links a:hover {
        color: #93c5fd;
    }
    
    .footer-links i {
        width: 20px;
        text-align: center;
    }
    
    .footer-bottom {
        background-color: #172554;
        padding: 1rem 2rem;
        text-align: center;
        display: flex;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 1rem;
    }
    
    .footer-bottom p {
        margin: 0;
        font-size: 0.9rem;
    }
    
    .version {
        color: #93c5fd;
    }
    
    @media (max-width: 768px) {
        .footer-content {
            grid-template-columns: 1fr;
            padding: 0 1.5rem 1.5rem;
        }
        
        .footer-bottom {
            flex-direction: column;
            text-align: center;
            gap: 0.5rem;
        }
    }
</style>

<!-- Include Font Awesome if not already included -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">