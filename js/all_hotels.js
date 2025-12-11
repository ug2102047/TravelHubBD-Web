// Price range slider for all_hotels.php
document.addEventListener('DOMContentLoaded', function() {
    const priceSliderMaxAll = document.getElementById('price-slider-max-all-hotels');
    const priceSliderValueAll = document.getElementById('price-slider-value-all-hotels');
    const priceMaxInputAll = document.getElementById('price_max_input_all_hotels');

    if (priceSliderMaxAll && priceSliderValueAll && priceMaxInputAll) {
        priceSliderMaxAll.oninput = function() {
            priceSliderValueAll.textContent = this.value;
            priceMaxInputAll.value = this.value;
        }
        priceMaxInputAll.onchange = function() {
            let val = parseInt(this.value, 10);
            const min = parseInt(priceSliderMaxAll.min, 10);
            const max = parseInt(priceSliderMaxAll.max, 10);
            if (isNaN(val) || val < min) val = min;
            else if (val > max) val = max;
            this.value = val;
            priceSliderMaxAll.value = val;
            priceSliderValueAll.textContent = val;
        }
    }
});