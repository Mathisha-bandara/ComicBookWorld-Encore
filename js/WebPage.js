let cart =
    JSON.parse(localStorage.getItem('cart')) || [];


// ==================================================
// PAGE LOAD
// ==================================================

window.addEventListener('DOMContentLoaded', function()
{
    cart =
        JSON.parse(localStorage.getItem('cart')) || [];

    updateCartCount();

    if (document.getElementById('cart-items'))
    {
        loadCart();
    }
});


// ==================================================
// CART COUNT
// ==================================================

function updateCartCount()
{
    let cartCount =
        document.getElementById('cart-count');

    if (!cartCount)
    {
        return;
    }

    let totalQuantity = 0;

    cart.forEach(function(item)
    {
        totalQuantity += Number(item.quantity) || 1;
    });

    cartCount.innerText = totalQuantity;
}


// ==================================================
// ADD TO CART
// ==================================================

function addToCart(
    productId,
    itemName,
    itemPrice,
    stock = null,
    showMessage = true
)
{
    productId = Number(productId);

    itemPrice = Number(itemPrice);

    if (stock !== null)
    {
        stock = Number(stock);
    }


    let existingItem =
        cart.find(function(item)
        {
            return Number(item.id) === productId;
        });


    let currentQuantity =
        existingItem
            ? Number(existingItem.quantity) || 0
            : 0;


    /*
        Prevent adding more than
        the available stock.
    */

    if (
        stock !== null &&
        currentQuantity >= stock
    )
    {
        if (showMessage)
        {
            alert(
                "Only " +
                stock +
                " copies are available. " +
                "You already have " +
                currentQuantity +
                " in your cart."
            );
        }

        return false;
    }


    /*
        Add one copy.
    */

    if (existingItem)
    {
        existingItem.quantity =
            currentQuantity + 1;

        /*
            Keep stock information
            updated in the cart.
        */

        if (stock !== null)
        {
            existingItem.stock = stock;
        }
    }
    else
    {
        cart.push({
            id: productId,
            name: itemName,
            price: itemPrice,
            quantity: 1,
            stock: stock
        });
    }


    localStorage.setItem(
        'cart',
        JSON.stringify(cart)
    );


    updateCartCount();


    if (showMessage)
    {
        alert(
            itemName +
            " added to cart!"
        );
    }


    return true;
}
// ==================================================
// LOAD CART
// ==================================================

function loadCart()
{
    let cartItems =
        JSON.parse(localStorage.getItem('cart')) || [];

    cart = cartItems;

    let container =
        document.getElementById('cart-items');

    if (!container)
    {
        return;
    }

    container.innerHTML = "";

    let total = 0;

    if (cartItems.length === 0)
    {
        container.innerHTML =
            "<p>Your cart is empty.</p>";

        let totalElement =
            document.getElementById('cart-total');

        if (totalElement)
        {
            totalElement.innerText =
                "Total: LKR 0.00";
        }

        let orderTotal =
            document.getElementById('order-total');

        if (orderTotal)
        {
            orderTotal.value = "0";
        }

        let cartData =
            document.getElementById('cart-data');

        if (cartData)
        {
            cartData.value = "";
        }

        updateCartCount();

        return;
    }


    cartItems.forEach(function(item, index)
    {
        let quantity =
            Number(item.quantity) || 1;

        let price =
            Number(item.price) || 0;

        let subtotal =
            price * quantity;

        total += subtotal;


       let productInfo =
    window.productCatalog &&
    window.productCatalog[item.id]
        ? window.productCatalog[item.id]
        : null;

let image =
    productInfo && productInfo.image
        ? productInfo.image
        : "";

let imageHtml = image
    ? `
        <div class="cart-item-image">
            <img
                src="Product/${escapeHtml(image)}"
                alt="${escapeHtml(item.name)}"
            >
        </div>
      `
    : `
        <div class="cart-item-image cart-no-image">
            <span>📚</span>
        </div>
      `;


container.innerHTML += `
    <article class="cart-item">

        ${imageHtml}

        <div class="cart-item-details">

            <p class="cart-item-category">
                Comic Book
            </p>

            <h3>
                ${escapeHtml(item.name)}
            </h3>

            <p class="cart-item-price">
                LKR ${price.toFixed(2)}
            </p>

            <div class="cart-quantity">

                <button
                    type="button"
                    onclick="changeQuantity(${index}, -1)"
                    aria-label="Decrease quantity"
                >
                    −
                </button>

                <span>
                    ${quantity}
                </span>

                <button
                    type="button"
                    onclick="changeQuantity(${index}, 1)"
                    aria-label="Increase quantity"
                >
                    +
                </button>

            </div>

        </div>


        <div class="cart-item-actions">

            <strong class="cart-item-subtotal">
                LKR ${subtotal.toFixed(2)}
            </strong>

            <button
                type="button"
                class="cart-remove-button"
                onclick="removeItem(${index})"
            >
                Remove
            </button>

        </div>

    </article>
    `;
    });


    let totalElement =
        document.getElementById('cart-total');

    if (totalElement)
    {
        totalElement.innerText =
            "Total: LKR " + total.toFixed(2);
    }

    let summarySubtotal =
    document.getElementById('summary-subtotal');

if (summarySubtotal)
{
    summarySubtotal.innerText =
        "LKR " + total.toFixed(2);
}


let summaryItemCount =
    document.getElementById('summary-item-count');

if (summaryItemCount)
{
    let itemCount = 0;

    cartItems.forEach(function(item)
    {
        itemCount +=
            Number(item.quantity) || 1;
    });

    summaryItemCount.innerText =
        itemCount;
}


    let orderTotal =
        document.getElementById('order-total');

    if (orderTotal)
    {
        orderTotal.value =
            total.toFixed(2);
    }


    let cartData =
        document.getElementById('cart-data');

    if (cartData)
    {
        cartData.value =
            JSON.stringify(cartItems);
    }


    updateCartCount();
}


// ==================================================
// CHANGE QUANTITY
// ==================================================

function changeQuantity(index, change)
{
    let cartItems =
        JSON.parse(localStorage.getItem('cart')) || [];

    if (!cartItems[index])
    {
        return;
    }


    let currentQuantity =
        Number(cartItems[index].quantity) || 1;


    let newQuantity =
        currentQuantity + Number(change);


    if (newQuantity <= 0)
    {
        cartItems.splice(index, 1);
    }
    else if (newQuantity <= 99)
    {
        cartItems[index].quantity =
            newQuantity;
    }


    localStorage.setItem(
        'cart',
        JSON.stringify(cartItems)
    );


    cart =
        cartItems;


    updateCartCount();

    loadCart();
}


// ==================================================
// REMOVE ENTIRE PRODUCT
// ==================================================

function removeItem(index)
{
    let cartItems =
        JSON.parse(localStorage.getItem('cart')) || [];


    if (!cartItems[index])
    {
        return;
    }


    cartItems.splice(index, 1);


    localStorage.setItem(
        'cart',
        JSON.stringify(cartItems)
    );


    cart =
        cartItems;


    updateCartCount();

    loadCart();
}


// ==================================================
// CLEAR CART
// ==================================================

function clearCart()
{
    localStorage.removeItem('cart');

    cart = [];

    updateCartCount();

    loadCart();
}


// ==================================================
// ESCAPE HTML
// ==================================================

function escapeHtml(value)
{
    return String(value)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

document.addEventListener('DOMContentLoaded', function()
{
    const productCards = document.querySelectorAll(
        '.product[data-product-url]'
    );

    productCards.forEach(function(card)
    {
        card.addEventListener('click', function(event)
        {
            if (event.target.closest('button'))
            {
                return;
            }

            window.location.href = card.dataset.productUrl;
        });


        card.addEventListener('keydown', function(event)
        {
            if (event.key === 'Enter' || event.key === ' ')
            {
                event.preventDefault();

                window.location.href =
                    card.dataset.productUrl;
            }
        });
    });
});

/* ==========================================
   COMIC SPOTLIGHT CAROUSEL
========================================== */

document.addEventListener('DOMContentLoaded', function()
{
    const viewport =
        document.querySelector('.spotlight-viewport');

    const track =
        document.querySelector('.spotlight-track');

    const originalCards =
        Array.from(
            document.querySelectorAll('.spotlight-card')
        );

    const previousButton =
        document.querySelector('.spotlight-prev');

    const nextButton =
        document.querySelector('.spotlight-next');

    const dotsContainer =
        document.querySelector('.spotlight-dots');


    if (
        !viewport ||
        !track ||
        originalCards.length === 0 ||
        !previousButton ||
        !nextButton
    )
    {
        return;
    }


    let cardsPerView = getCardsPerView();

    let currentIndex = 0;

    let autoSlide;

    let isAnimating = false;


    /*
        Create cloned cards.

        Example:

        Original:
        1 2 3 4 5 6 7 8

        Clones:
        1 2 3 4
    */

    function createClones()
    {
        const existingClones =
            track.querySelectorAll(
                '.spotlight-card-clone'
            );

        existingClones.forEach(function(clone)
        {
            clone.remove();
        });


        cardsPerView = getCardsPerView();


        const cloneCount =
            Math.min(
                cardsPerView,
                originalCards.length
            );


        for (
            let i = 0;
            i < cloneCount;
            i++
        )
        {
            const clone =
                originalCards[i].cloneNode(true);

            clone.classList.add(
                'spotlight-card-clone'
            );

            track.appendChild(clone);
        }
    }


    function getCardsPerView()
    {
        if (window.innerWidth <= 600)
        {
            return 1;
        }

        if (window.innerWidth <= 900)
        {
            return 2;
        }

        if (window.innerWidth <= 1100)
        {
            return 3;
        }

        return 4;
    }


    function getCardWidth()
    {
        return 100 / cardsPerView;
    }


    function updatePosition(animate)
    {
        const cardWidth =
            getCardWidth();


        track.style.transition =
            animate
                ? 'transform 0.6s ease'
                : 'none';


        track.style.transform =
            'translateX(-' +
            (currentIndex * cardWidth) +
            '%)';
    }


    function nextSlide()
    {
        if (isAnimating)
        {
            return;
        }


        isAnimating = true;

        currentIndex++;


        updatePosition(true);


        /*
            Once we reach the cloned cards,
            wait for the animation to finish,
            then silently jump back to
            the original cards.
        */

        if (
            currentIndex >= originalCards.length
        )
        {
            setTimeout(function()
            {
                currentIndex = 0;

                updatePosition(false);

                isAnimating = false;

            }, 650);
        }
        else
        {
            setTimeout(function()
            {
                isAnimating = false;

            }, 650);
        }
    }


    function previousSlide()
    {
        if (isAnimating)
        {
            return;
        }


        /*
            If we're at the beginning,
            jump silently to the equivalent
            position near the cloned section.
        */

        if (currentIndex <= 0)
        {
            currentIndex =
                originalCards.length;

            updatePosition(false);
        }


        isAnimating = true;

        currentIndex--;

        updatePosition(true);


        setTimeout(function()
        {
            isAnimating = false;

        }, 650);
    }


    function createDots()
    {
        if (!dotsContainer)
        {
            return;
        }


        dotsContainer.innerHTML = "";


        /*
            One dot for each original product.
        */

        for (
            let i = 0;
            i < originalCards.length;
            i++
        )
        {
            const dot =
                document.createElement('button');


            dot.type = 'button';

            dot.className =
                'spotlight-dot';


            dot.setAttribute(
                'aria-label',
                'Show comic ' + (i + 1)
            );


            dot.addEventListener(
                'click',
                function()
                {
                    if (isAnimating)
                    {
                        return;
                    }


                    currentIndex = i;

                    updatePosition(true);

                    restartAutoSlide();
                }
            );


            dotsContainer.appendChild(dot);
        }
    }


    function updateDots()
    {
        if (!dotsContainer)
        {
            return;
        }


        const dots =
            dotsContainer.querySelectorAll(
                '.spotlight-dot'
            );


        dots.forEach(function(dot, index)
        {
            dot.classList.toggle(
                'active',
                index ===
                (currentIndex %
                    originalCards.length)
            );
        });
    }


    function startAutoSlide()
    {
        clearInterval(autoSlide);


        if (originalCards.length <= cardsPerView)
        {
            return;
        }


        autoSlide =
            setInterval(
                function()
                {
                    nextSlide();
                    updateDots();

                },
                4500
            );
    }


    function restartAutoSlide()
    {
        startAutoSlide();
    }


    nextButton.addEventListener(
        'click',
        function()
        {
            nextSlide();

            restartAutoSlide();
        }
    );


    previousButton.addEventListener(
        'click',
        function()
        {
            previousSlide();

            restartAutoSlide();
        }
    );


    viewport.addEventListener(
        'mouseenter',
        function()
        {
            clearInterval(autoSlide);
        }
    );


    viewport.addEventListener(
        'mouseleave',
        function()
        {
            startAutoSlide();
        }
    );


    window.addEventListener(
        'resize',
        function()
        {
            const newCardsPerView =
                getCardsPerView();


            if (
                newCardsPerView !== cardsPerView
            )
            {
                cardsPerView =
                    newCardsPerView;


                currentIndex = 0;


                createClones();

                updatePosition(false);

                createDots();

                updateDots();

                startAutoSlide();
            }
        }
    );


    /*
        Initial setup
    */

    createClones();

    createDots();

    updatePosition(false);

    updateDots();

    startAutoSlide();
});

/* ==========================================
   PROMO CODE
========================================== */

document.addEventListener(
    'DOMContentLoaded',
    function()
    {
        const promoButton =
            document.getElementById(
                'apply-promo-button'
            );


        const promoInput =
            document.getElementById(
                'promo-code'
            );


        const promoMessage =
            document.getElementById(
                'promo-message'
            );


        const promoHidden =
            document.getElementById(
                'promo-code-hidden'
            );


        const discountRow =
            document.getElementById(
                'discount-row'
            );


        const discountAmount =
            document.getElementById(
                'discount-amount'
            );


        if (
            !promoButton ||
            !promoInput
        )
        {
            return;
        }


        promoButton.addEventListener(
            'click',
            function()
            {
                applyPromoCode();
            }
        );


        promoInput.addEventListener(
            'keydown',
            function(event)
            {
                if (event.key === 'Enter')
                {
                    event.preventDefault();

                    applyPromoCode();
                }
            }
        );


        function applyPromoCode()
        {
            const code =
                promoInput.value
                    .trim()
                    .toUpperCase();


            if (code === '')
            {
                showPromoMessage(
                    'Please enter a promo code.',
                    false
                );

                return;
            }


            const cartData =
                JSON.stringify(cart);


            const csrfInput =
                document.querySelector(
                    'input[name="csrf_token"]'
                );


            if (!csrfInput)
            {
                showPromoMessage(
                    'Security token not found.',
                    false
                );

                return;
            }


            promoButton.disabled = true;

            promoButton.innerText =
                'Applying...';


            const formData =
                new FormData();


            formData.append(
                'promo_code',
                code
            );


            formData.append(
                'cart_data',
                cartData
            );


            formData.append(
                'csrf_token',
                csrfInput.value
            );


            fetch(
                'apply_promo.php',
                {
                    method: 'POST',
                    body: formData
                }
            )
            .then(function(response)
            {
                return response.json();
            })
            .then(function(data)
            {
                if (data.success)
                {
                    promoHidden.value =
                        data.code;


                    discountAmount.innerText =
                        '-LKR ' +
                        Number(
                            data.discount
                        ).toFixed(2);


                    discountRow.style.display =
                        'flex';


                    showPromoMessage(
                        data.message,
                        true
                    );


                    const totalElement =
                        document.getElementById(
                            'cart-total'
                        );


                    if (totalElement)
                    {
                        totalElement.innerText =
                            'Total: LKR ' +
                            Number(
                                data.total
                            ).toFixed(2);
                    }


                    const orderTotal =
                        document.getElementById(
                            'order-total'
                        );


                    if (orderTotal)
                    {
                        orderTotal.value =
                            Number(
                                data.total
                            ).toFixed(2);
                    }
                }
                else
                {
                    promoHidden.value = '';

                    discountRow.style.display =
                        'none';


                    showPromoMessage(
                        data.message,
                        false
                    );
                }
            })
            .catch(function()
            {
                showPromoMessage(
                    'Unable to apply the promo code. Please try again.',
                    false
                );
            })
            .finally(function()
            {
                promoButton.disabled =
                    false;

                promoButton.innerText =
                    'Apply';
            });
        }


        function showPromoMessage(
            message,
            success
        )
        {
            if (!promoMessage)
            {
                return;
            }


            promoMessage.innerText =
                message;


            promoMessage.classList.toggle(
                'promo-success',
                success
            );


            promoMessage.classList.toggle(
                'promo-error',
                !success
            );
        }
    }
);