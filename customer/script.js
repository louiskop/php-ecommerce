

// handle active category
document.addEventListener("DOMContentLoaded", (event) => {

    // get all categories
    const cats = document.querySelectorAll(".category");
    
    // change active category on click
    let i = 0;
    for (i; i < cats.length; i++) {
        
        cats[i].addEventListener('click', (event) => {
            
            // remove active category from all
            // let j = 0;
            // for (j; j < cats.length; j++) {
            //     cats[j].classList.remove('active-category');
            // }
            
            // add only to this one
            // event.target.classList.add('active-category');
            // console.log(event.target);

            // trigger form submit
            var form = document.createElement("form");
            form.method = "POST";
            form.action = "customer.php"; 
            var input = document.createElement("input");
            input.type = "hidden";
            input.name = "filter"; 
            input.value = event.target.innerHTML; 
            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();

        });
        
    } 


    // add event listener for add to cart
    const btns = document.querySelectorAll('.add-to-cart');
    let l = 0;
    for(l; l < btns.length; l++) {
        btns[l].addEventListener('click', async (event) => {
            alert("Item added to cart");
        })
    }


});


// logout function
function logout() {
    window.location.href = "/agora/login/logout.php";
}

// cart function
function viewcart() {
    window.location.href = "/agora/cart/cart.php";
}

// view wallet function
function viewwallet() {
    window.location.href = "/agora/wallet/wallet.php";
}

// view orders function
function vieworders() {
    window.location.href = "/agora/orders/orders.php";
}
