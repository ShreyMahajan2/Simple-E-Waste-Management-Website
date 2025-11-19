// Location services for e-waste management system

// Get current location using browser geolocation
function getCurrentLocation() {
    const status = document.getElementById('pickupLocationStatus');
    status.innerHTML = '<span class="text-warning">🔄 Getting your location...</span>';
    
    if (!navigator.geolocation) {
        status.innerHTML = '<span class="text-danger">❌ Geolocation is not supported by this browser.</span>';
        return;
    }
    
    navigator.geolocation.getCurrentPosition(
        function(position) {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;
            
            // Set coordinates
            document.getElementById('pickup_latitude').value = lat.toFixed(6);
            document.getElementById('pickup_longitude').value = lng.toFixed(6);
            
            // Reverse geocode to get address
            reverseGeocode(lat, lng);
            
            status.innerHTML = '<span class="text-success">✅ Location set successfully!</span>';
        },
        function(error) {
            let errorMessage = '❌ Unable to get your location. ';
            switch(error.code) {
                case error.PERMISSION_DENIED:
                    errorMessage += 'Please allow location access.';
                    break;
                case error.POSITION_UNAVAILABLE:
                    errorMessage += 'Location information is unavailable.';
                    break;
                case error.TIMEOUT:
                    errorMessage += 'Location request timed out.';
                    break;
                default:
                    errorMessage += 'An unknown error occurred.';
                    break;
            }
            status.innerHTML = '<span class="text-danger">' + errorMessage + '</span>';
        },
        {
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 60000
        }
    );
}

// Reverse geocode coordinates to address
function reverseGeocode(lat, lng) {
    // Using OpenStreetMap Nominatim API (free)
    const url = `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`;
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data && data.display_name) {
                document.getElementById('pickup_address').value = data.display_name;
            } else {
                document.getElementById('pickup_address').value = `Near coordinates: ${lat.toFixed(6)}, ${lng.toFixed(6)}`;
            }
        })
        .catch(error => {
            console.error('Geocoding error:', error);
            document.getElementById('pickup_address').value = `Location at: ${lat.toFixed(6)}, ${lng.toFixed(6)}`;
        });
}

// Use saved user location (from registration)
function useSavedLocation() {
    // This would typically be populated from user profile
    // For now, we'll simulate it
    const status = document.getElementById('pickupLocationStatus');
    status.innerHTML = '<span class="text-warning">🔄 Loading saved location...</span>';
    
    // Simulate API call to get user's saved location
    setTimeout(() => {
        // In a real app, you'd fetch this from user profile
        const savedLat = document.getElementById('pickup_latitude').dataset.savedLat;
        const savedLng = document.getElementById('pickup_longitude').dataset.savedLng;
        const savedAddress = document.getElementById('pickup_address').dataset.savedAddress;
        
        if (savedLat && savedLng) {
            document.getElementById('pickup_latitude').value = savedLat;
            document.getElementById('pickup_longitude').value = savedLng;
            if (savedAddress) {
                document.getElementById('pickup_address').value = savedAddress;
            }
            status.innerHTML = '<span class="text-success">✅ Saved location loaded!</span>';
        } else {
            status.innerHTML = '<span class="text-warning">⚠ No saved location found. Please set a location first.</span>';
        }
    }, 1000);
}

// Open map picker (simplified version)
function openMapPicker() {
    const status = document.getElementById('pickupLocationStatus');
    status.innerHTML = '<span class="text-info">🗺️ Open map to pick location...</span>';
    
    // Get current values or use defaults
    let lat = document.getElementById('pickup_latitude').value || '40.7128';
    let lng = document.getElementById('pickup_longitude').value || '-74.0060';
    
    // Open in new window with map (simplified)
    const mapUrl = `https://www.openstreetmap.org/?mlat=${lat}&mlon=${lng}&zoom=15`;
    window.open(mapUrl, 'MapPicker', 'width=800,height=600');
    
    // In a real app, you'd use a proper map picker modal
    status.innerHTML = '<span class="text-info">📍 After picking location on map, enter coordinates manually or use current location.</span>';
}

// For registration page
function getLocation() {
    const status = document.getElementById('locationStatus');
    status.innerHTML = '<span class="text-warning">🔄 Getting your location...</span>';
    
    if (!navigator.geolocation) {
        status.innerHTML = '<span class="text-danger">❌ Geolocation not supported.</span>';
        return;
    }
    
    navigator.geolocation.getCurrentPosition(
        function(position) {
            document.getElementById('latitude').value = position.coords.latitude.toFixed(6);
            document.getElementById('longitude').value = position.coords.longitude.toFixed(6);
            status.innerHTML = '<span class="text-success">✅ Location set! You can update this in your profile.</span>';
        },
        function(error) {
            status.innerHTML = '<span class="text-danger">❌ Could not get location. You can set it later.</span>';
        }
    );
}

function pickOnMap() {
    const status = document.getElementById('locationStatus');
    status.innerHTML = '<span class="text-info">🗺️ You can set location in your profile after registration.</span>';
}

// Initialize location services
document.addEventListener('DOMContentLoaded', function() {
    // Check if we're on a page that needs location
    if (document.getElementById('pickup_latitude')) {
        // Try to get approximate location on page load
        setTimeout(() => {
            if (!document.getElementById('pickup_latitude').value) {
                document.getElementById('pickupLocationStatus').innerHTML = 
                    '<span class="text-info">📍 Click above to set pickup location for accurate collection.</span>';
            }
        }, 1000);
    }
});