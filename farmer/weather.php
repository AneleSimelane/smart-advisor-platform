<?php
require_once __DIR__ . '/../config/core.php';
requireRole('farmer');

$page_title = "Local Weather Forecast";
require_once __DIR__ . '/../includes/header.php';
?>

<div class="dashboard-wrapper">
    <?php require_once __DIR__ . '/../includes/farmer_sidebar.php'; ?>
    
    <div class="dashboard-content">
        <h2 class="mb-4"><i class="fas fa-cloud-sun text-primary me-2"></i>Weather Forecast</h2>
        <p class="text-muted mb-4">Plan your farming activities with localized weather data.</p>

        <div id="weatherError" class="alert alert-danger" style="display: none;">
            Unable to fetch weather data. Please check your internet connection.
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-body p-5 text-center" id="currentWeatherLoading">
                <div class="spinner-border text-primary mb-3" role="status"></div>
                <h5>Loading latest weather data...</h5>
            </div>
            
            <div class="card-body" id="currentWeatherContent" style="display: none;">
                <div class="row align-items-center">
                    <div class="col-md-6 text-center text-md-start border-md-end mb-4 mb-md-0">
                        <h4 class="text-muted fw-normal mb-1">Current Conditions</h4>
                        <h2 class="fw-bold mb-3">Mbabané, Eswatini</h2>
                        <div class="d-flex align-items-center justify-content-center justify-content-md-start">
                            <i id="cwIcon" class="fas fa-sun text-warning fa-4x me-4"></i>
                            <h1 class="display-1 fw-bold mb-0" id="cwTemp">--°C</h1>
                        </div>
                        <p class="lead mt-3 mb-0" id="cwDesc">Clear sky</p>
                    </div>
                    <div class="col-md-6 ps-md-5">
                        <div class="row g-4">
                            <div class="col-6">
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary bg-opacity-10 rounded p-3 me-3">
                                        <i class="fas fa-tint text-primary fa-2x"></i>
                                    </div>
                                    <div>
                                        <p class="text-muted mb-0 small text-uppercase fw-bold">Humidity</p>
                                        <h4 class="mb-0 fw-bold" id="cwHumidity">--%</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="d-flex align-items-center">
                                    <div class="bg-secondary bg-opacity-10 rounded p-3 me-3">
                                        <i class="fas fa-wind text-secondary fa-2x"></i>
                                    </div>
                                    <div>
                                        <p class="text-muted mb-0 small text-uppercase fw-bold">Wind</p>
                                        <h4 class="mb-0 fw-bold" id="cwWind">-- km/h</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="d-flex align-items-center">
                                    <div class="bg-info bg-opacity-10 rounded p-3 me-3">
                                        <i class="fas fa-cloud-showers-heavy text-info fa-2x"></i>
                                    </div>
                                    <div>
                                        <p class="text-muted mb-0 small text-uppercase fw-bold">Rainfall</p>
                                        <h4 class="mb-0 fw-bold" id="cwRain">-- mm</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="d-flex align-items-center">
                                    <div class="bg-warning bg-opacity-10 rounded p-3 me-3">
                                        <i class="fas fa-sun text-warning fa-2x"></i>
                                    </div>
                                    <div>
                                        <p class="text-muted mb-0 small text-uppercase fw-bold">UV Index</p>
                                        <h4 class="mb-0 fw-bold">High</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <h4 class="mb-3">7-Day Forecast</h4>
        <div class="row" id="forecastContainer">
            <!-- Populated by JS -->
        </div>

    </div>
</div>

<?php 
$extra_js = <<<'EOT'
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Eswatini Coordinates
    const lat = -26.3054;
    const lon = 31.1367;
    const weatherUrl = `https://api.open-meteo.com/v1/forecast?latitude=${lat}&longitude=${lon}&current=temperature_2m,relative_humidity_2m,precipitation,wind_speed_10m,weathercode,is_day&daily=weathercode,temperature_2m_max,temperature_2m_min,precipitation_sum&timezone=Africa%2FJohannesburg`;

    // Basic WMO weather code mapping to FontAwesome icons
    const weatherIcons = {
        0: 'fa-sun text-warning', // Clear sky
        1: 'fa-cloud-sun text-warning', // Mainly clear
        2: 'fa-cloud-sun text-secondary', // Partly cloudy
        3: 'fa-cloud text-secondary', // Overcast
        45: 'fa-smog text-secondary', // Fog
        48: 'fa-smog text-secondary',
        51: 'fa-cloud-rain text-info', // Drizzle
        53: 'fa-cloud-rain text-info',
        55: 'fa-cloud-rain text-info',
        61: 'fa-cloud-showers-heavy text-primary', // Rain
        63: 'fa-cloud-showers-heavy text-primary',
        65: 'fa-cloud-showers-heavy text-primary',
        80: 'fa-cloud-showers-heavy text-primary', // Rain showers
        95: 'fa-poo-storm text-danger', // Thunderstorm
    };
    
    const weatherDesc = {
        0: 'Clear sky', 1: 'Mainly clear', 2: 'Partly cloudy', 3: 'Overcast',
        45: 'Fog', 51: 'Light Drizzle', 61: 'Rain', 65: 'Heavy Rain', 
        80: 'Rain Showers', 95: 'Thunderstorm'
    };

    function getIcon(code, isDay = 1) {
        if (code === 0 && isDay === 0) return 'fa-moon text-secondary';
        return weatherIcons[code] || 'fa-cloud text-secondary';
    }

    function getDesc(code) {
        return weatherDesc[code] || 'Variable';
    }

    fetch(weatherUrl)
        .then(response => {
            if(!response.ok) throw new Error('Network error');
            return response.json();
        })
        .then(data => {
            document.getElementById('currentWeatherLoading').style.display = 'none';
            document.getElementById('currentWeatherContent').style.display = 'block';
            
            // Populate Current Weather
            const current = data.current;
            document.getElementById('cwTemp').innerText = current.temperature_2m + '°C';
            document.getElementById('cwHumidity').innerText = current.relative_humidity_2m + '%';
            document.getElementById('cwWind').innerText = current.wind_speed_10m + ' km/h';
            document.getElementById('cwRain').innerText = current.precipitation + ' mm';
            document.getElementById('cwDesc').innerText = getDesc(current.weathercode);
            
            const iconEl = document.getElementById('cwIcon');
            iconEl.className = 'fas fa-4x me-4 ' + getIcon(current.weathercode, current.is_day);

            // Populate Daily Forecast
            const daily = data.daily;
            let forecastHTML = '';
            
            for(let i=0; i < 7; i++) {
                const dateObj = new Date(daily.time[i]);
                const dayName = dateObj.toLocaleDateString('en-US', { weekday: 'short' });
                const iconClass = getIcon(daily.weathercode[i], 1);
                const desc = getDesc(daily.weathercode[i]);
                
                forecastHTML += `
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="card shadow-sm h-100 text-center border-0">
                        <div class="card-body">
                            <h5 class="fw-bold text-muted">${i === 0 ? 'Today' : dayName}</h5>
                            <i class="fas ${iconClass} fa-3x my-3"></i>
                            <p class="mb-2 small">${desc}</p>
                            <div class="d-flex justify-content-between px-2">
                                <span class="fw-bold text-danger">${daily.temperature_2m_max[i]}°</span>
                                <span class="fw-bold text-primary">${daily.temperature_2m_min[i]}°</span>
                            </div>
                            <div class="small text-muted mt-2">
                                <i class="fas fa-tint text-info me-1"></i>${daily.precipitation_sum[i]} mm
                            </div>
                        </div>
                    </div>
                </div>`;
            }
            document.getElementById('forecastContainer').innerHTML = forecastHTML;
        })
        .catch(error => {
            document.getElementById('currentWeatherLoading').style.display = 'none';
            document.getElementById('weatherError').style.display = 'block';
        });
});
</script>
EOT;
require_once __DIR__ . '/../includes/footer.php'; 
?>
