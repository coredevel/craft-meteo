document.addEventListener('DOMContentLoaded', function() {
    const searchBtn = document.getElementById('search-location');
    const input = document.getElementById('location-search');
    const resultsDiv = document.getElementById('location-results');
    const spinner = document.getElementById('latlong-spinner');
    if (!searchBtn || !input || !resultsDiv || !spinner) return;
    function showSpinner(show) {
        spinner.classList.toggle('hidden', !show);
    }
    async function doSearch() {
        const query = input.value.trim();
        if (!query) return;
        showSpinner(true);
        resultsDiv.innerHTML = '';
        try {
            const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}`;
            const res = await fetch(url);
            const data = await res.json();
            if (!data.length) {
                resultsDiv.innerHTML = 'No results found.';
                return;
            }
            resultsDiv.innerHTML = data.map(place =>
                `<div style='margin-bottom:6px;'>
                    <strong>${place.display_name}</strong><br>
                    Lat: <a href="#" class="latlng-pick" data-lat="${place.lat}" data-lon="${place.lon}">${place.lat}</a>,
                    Lon: <a href="#" class="latlng-pick" data-lat="${place.lat}" data-lon="${place.lon}">${place.lon}</a>
                    <button type="button" class="pick-latlng" data-lat="${place.lat}" data-lon="${place.lon}">Use</button>
                </div>`
            ).join('');
            resultsDiv.querySelectorAll('.pick-latlng').forEach(btn => {
                btn.addEventListener('click', function() {
                    document.getElementById('latitude').value = this.dataset.lat;
                    document.getElementById('longitude').value = this.dataset.lon;
                });
            });
        } catch (e) {
            resultsDiv.innerHTML = '<span style="color:red">Error fetching results.</span>';
        } finally {
            showSpinner(false);
        }
    }
    searchBtn.addEventListener('click', doSearch);
    input.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            doSearch();
        }
    });
});
