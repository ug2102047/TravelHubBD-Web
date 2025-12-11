// Booking form functionality for book_hotel.php
document.addEventListener('DOMContentLoaded', () => {
    const checkInDateElem = document.getElementById('check_in_date_input');
    const checkOutDateElem = document.getElementById('check_out_date_input');
    const totalPriceDisplayElem = document.getElementById('total-price-display');
    const totalNightsDisplayElem = document.getElementById('total-nights-display');
    const pricePerNightHiddenElem = document.getElementById('price_per_night_for_booking_hidden');

    // Function to calculate nights
    function calculateNights(checkInStr, checkOutStr) {
        if (!checkInStr || !checkOutStr) return 0;
        
        const date1 = new Date(checkInStr);
        const date2 = new Date(checkOutStr);

        if (isNaN(date1.getTime()) || isNaN(date2.getTime()) || date2 <= date1) {
            return 0; 
        }
        const diffTime = Math.abs(date2 - date1);
        return Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    }

    // Function to update price display
    function updateBookingPriceAndNights() {
        if (!checkInDateElem || !checkOutDateElem || !totalPriceDisplayElem || !pricePerNightHiddenElem || !totalNightsDisplayElem) {
            return;
        }

        const nights = calculateNights(checkInDateElem.value, checkOutDateElem.value);
        const pricePerNight = parseFloat(pricePerNightHiddenElem.value);

        if (nights > 0 && !isNaN(pricePerNight)) {
            const totalBookingPrice = pricePerNight * nights;
            totalPriceDisplayElem.textContent = totalBookingPrice.toFixed(2);
            totalNightsDisplayElem.textContent = nights;
        } else {
            totalPriceDisplayElem.textContent = "0.00";
            totalNightsDisplayElem.textContent = "0";
        }
    }
    
    // Initialize Flatpickr date pickers if elements exist
    if (checkInDateElem) {
        const fpCheckIn = flatpickr(checkInDateElem, {
            minDate: "today",
            dateFormat: "Y-m-d",
            onChange: function(selectedDates, dateStr, instance) {
                if (fpCheckOut) {
                    fpCheckOut.set('minDate', dateStr);
                    if (fpCheckOut.selectedDates.length > 0 && new Date(fpCheckOut.selectedDates[0]) <= new Date(dateStr)) {
                        fpCheckOut.clear();
                    }
                }
                updateBookingPriceAndNights();
            }
        });
    }

    if (checkOutDateElem) {
        const fpCheckOut = flatpickr(checkOutDateElem, {
            dateFormat: "Y-m-d",
            onChange: function(selectedDates, dateStr, instance) {
                updateBookingPriceAndNights();
            }
        });
    }
    
    // Initial calculation on page load
    updateBookingPriceAndNights();
});