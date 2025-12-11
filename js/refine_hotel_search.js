// Price range slider for refine_hotel_search.php
document.addEventListener('DOMContentLoaded', function() {
    const priceSliderMaxRefine = document.getElementById('price-slider-max-refine');
    const priceSliderValueRefine = document.getElementById('price-slider-value-refine');
    const priceMaxInputRefine = document.getElementById('price_max_input_refine');

    if (priceSliderMaxRefine && priceSliderValueRefine && priceMaxInputRefine) {
        priceSliderMaxRefine.oninput = function() {
            priceSliderValueRefine.textContent = this.value;
            priceMaxInputRefine.value = this.value;
        }
        priceMaxInputRefine.onchange = function() {
            let val = parseInt(this.value, 10);
            const min = parseInt(priceSliderMaxRefine.min, 10);
            const max = parseInt(priceSliderMaxRefine.max, 10);

            if (isNaN(val) || val < min) val = min;
            else if (val > max) val = max;
            
            this.value = val;
            priceSliderMaxRefine.value = val;
            priceSliderValueRefine.textContent = val;
        }
    }
});