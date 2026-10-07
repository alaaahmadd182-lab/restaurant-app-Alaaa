let editingRestaurantId = null;
function fillRestaurantForm(restaurant) {
    editingRestaurantId = restaurant.id;

    document.getElementById("restaurant-name").value =
        restaurant.name;

    document.getElementById("restaurant-description").value =
        restaurant.description || "";

    document.getElementById("restaurant-address").value =
        restaurant.address;

    document.getElementById("restaurant-category").value =
        restaurant.category_id;

    document.getElementById("restaurant-latitude").value =
        restaurant.latitude || "";

    document.getElementById("restaurant-longitude").value =
        restaurant.longitude || "";

    document.getElementById("restaurant-image").value =
        restaurant.image || "";

    document
        .querySelector("#restaurant-form button[type='submit']")
        .textContent = "Update Restaurant";

    document.getElementById("cancel-restaurant-edit").hidden = false;
}


async function checkAdmin() {
    const token = localStorage.getItem("token");

    if (!token) {
        window.location.href = "login.html";
        return;
    }

    try {
        const response = await apiRequest("/auth/me");
        const user = response.data;

        if (user.role !== "admin") {
            window.location.href = "index.html";
            return;
        }

        loadAdminRestaurants();

    } catch (error) {
        localStorage.removeItem("token");
        window.location.href = "login.html";
    }
}


async function loadAdminRestaurants() {
    const container =
        document.getElementById("admin-restaurants");

    try {
        const response = await apiRequest("/restaurants");
        const restaurants = response.data.restaurants;

        container.innerHTML = "";

        restaurants.forEach((restaurant) => {
            const restaurantBox = document.createElement("div");

            const name = document.createElement("h3");
            name.textContent = restaurant.name;

            const address = document.createElement("p");
            address.textContent = restaurant.address;

            const editButton = document.createElement("button");
            editButton.textContent = "Edit";

            editButton.addEventListener("click", () => {
                fillRestaurantForm(restaurant);
            });

            const deleteButton = document.createElement("button");
            deleteButton.textContent = "Delete";

            deleteButton.addEventListener("click", () => {
                deleteRestaurant(restaurant.id);
            });
            const menusButton = document.createElement("button");
            menusButton.textContent = "Manage Menus";

            menusButton.addEventListener("click", () => {
                loadAdminMenus(restaurant.id, restaurant.name);
            });
            restaurantBox.appendChild(name);
            restaurantBox.appendChild(address);
            restaurantBox.appendChild(editButton);
            restaurantBox.appendChild(deleteButton);
            restaurantBox.appendChild(menusButton);
            container.appendChild(restaurantBox);
        });

    } catch (error) {
        document.getElementById("admin-message").textContent =
            error.message;
    }
}


document
    .getElementById("logout-button")
    .addEventListener("click", () => {
        localStorage.removeItem("token");
        window.location.href = "login.html";
    });



async function loadAdminCategories() {
    try {
        const response = await apiRequest("/categories");
        const categories = response.data;

        const select =
            document.getElementById("restaurant-category");

        categories.forEach((category) => {
            const option = document.createElement("option");

            option.value = category.id;
            option.textContent = category.name;

            select.appendChild(option);
        });

    } catch (error) {
        document.getElementById("restaurant-message").textContent =
            error.message;
    }
}


const restaurantForm =
    document.getElementById("restaurant-form");

restaurantForm.addEventListener("submit", async (event) => {
    event.preventDefault();
    const submitButton =
    restaurantForm.querySelector("button[type='submit']");
    const message =
        document.getElementById("restaurant-message");

    const latitude =
        document.getElementById("restaurant-latitude").value;

    const longitude =
        document.getElementById("restaurant-longitude").value;

    const restaurant = {
        name:
            document.getElementById("restaurant-name").value.trim(),

        description:
            document.getElementById("restaurant-description").value.trim(),

        address:
            document.getElementById("restaurant-address").value.trim(),

        category_id:
            Number(
                document.getElementById("restaurant-category").value
            ),

        latitude:
            latitude === "" ? null : Number(latitude),

        longitude:
            longitude === "" ? null : Number(longitude),

        image:
            document.getElementById("restaurant-image").value.trim() || null
    };
    if (
            !restaurant.name ||
            !restaurant.address ||
            !restaurant.category_id
        ) {
            message.textContent =
                "Name, address and category are required.";
            return;
        }

        if (
            restaurant.latitude !== null &&
            (restaurant.latitude < -90 || restaurant.latitude > 90)
        ) {
            message.textContent =
                "Latitude must be between -90 and 90.";
            return;
        }

        if (
            restaurant.longitude !== null &&
            (restaurant.longitude < -180 || restaurant.longitude > 180)
        ) {
            message.textContent =
                "Longitude must be between -180 and 180.";
            return;
        }
        submitButton.disabled = true;
    try {
        if (editingRestaurantId) {

            await apiRequest(
                `/restaurants/${editingRestaurantId}`,
                {
                    method: "PUT",
                    body: JSON.stringify(restaurant)
                }
            );

            message.textContent =
                "Restaurant updated successfully.";

            editingRestaurantId = null;

        } else {

            await apiRequest("/restaurants", {
                method: "POST",
                body: JSON.stringify(restaurant)
            });

            message.textContent =
                "Restaurant added successfully.";
        }
        restaurantForm.reset();

        document.getElementById("cancel-restaurant-edit").hidden = true;

        document
            .querySelector("#restaurant-form button[type='submit']")
            .textContent = "Add Restaurant";

        loadAdminRestaurants();
    } catch (error) {
        message.textContent = error.message;

    } finally {
        submitButton.disabled = false;
    }
});

async function deleteRestaurant(id) {
    const confirmed = confirm(
        "Are you sure you want to delete this restaurant?"
    );

    if (!confirmed) {
        return;
    }

    try {
        await apiRequest(`/restaurants/${id}`, {
            method: "DELETE"
        });

        document.getElementById("restaurant-message").textContent =
            "Restaurant deleted successfully.";

        if (selectedRestaurantId === id) {
            selectedRestaurantId = null;
            selectedMenuId = null;
            editingMenuId = null;
            editingItemId = null;

            document.getElementById("menu-management").hidden = true;
            document.getElementById("item-management").hidden = true;
        }
        loadAdminRestaurants();


    } catch (error) {
        document.getElementById("restaurant-message").textContent =
            error.message;
    }
}




let selectedRestaurantId = null;
let editingMenuId = null;


async function loadAdminMenus(restaurantId, restaurantName) {
    selectedRestaurantId = restaurantId;
    editingMenuId = null;
    selectedMenuId = null;
    editingItemId = null;

    document.getElementById("item-management").hidden = true;
    const section = document.getElementById("menu-management");
    const container = document.getElementById("admin-menus");

    section.hidden = false;

    document.getElementById("menu-restaurant-name").textContent =
        `Menus - ${restaurantName}`;

    document.getElementById("menu-form").reset();
    document.getElementById("cancel-menu-edit").hidden = true;
    document
        .querySelector("#menu-form button[type='submit']")
        .textContent = "Add Menu";

    try {
        const response = await apiRequest(
            `/restaurants/${restaurantId}/menus`
        );

        const menus = response.data;

        container.innerHTML = "";

        if (menus.length === 0) {
            container.textContent = "No menus available.";
            return;
        }

        menus.forEach((menu) => {
            const box = document.createElement("div");

            const name = document.createElement("h3");
            name.textContent = menu.name;

            const editButton = document.createElement("button");
            editButton.textContent = "Edit";

            editButton.addEventListener("click", () => {
                editingMenuId = menu.id;

                document.getElementById("menu-name").value =
                    menu.name;

                document
                    .querySelector("#menu-form button[type='submit']")
                    .textContent = "Update Menu";

                document.getElementById("cancel-menu-edit").hidden = false;
            });

            const deleteButton = document.createElement("button");
            deleteButton.textContent = "Delete";


            deleteButton.addEventListener("click", () => {
                deleteMenu(menu.id);
            });

            const itemsButton = document.createElement("button");
            itemsButton.textContent = "Manage Items";

            itemsButton.addEventListener("click", () => {
                loadAdminItems(menu.id, menu.name);
            });
            box.appendChild(name);
            box.appendChild(editButton);
            box.appendChild(deleteButton);
            box.appendChild(itemsButton);

            container.appendChild(box);
        });

    } catch (error) {
        document.getElementById("menu-message").textContent =
            error.message;
    }
}

document
    .getElementById("menu-form")
    .addEventListener("submit", async (event) => {

        event.preventDefault();
        const submitButton =
        document.querySelector("#menu-form button[type='submit']");

        const name =
            document.getElementById("menu-name").value.trim();

        const message =
            document.getElementById("menu-message");
        submitButton.disabled = true;
        try {
            if (editingMenuId) {

                await apiRequest(`/menus/${editingMenuId}`, {
                    method: "PUT",
                    body: JSON.stringify({ name })
                });

                message.textContent = "Menu updated successfully.";
                editingMenuId = null;

            } else {

                await apiRequest(
                    `/restaurants/${selectedRestaurantId}/menus`,
                    {
                        method: "POST",
                        body: JSON.stringify({ name })
                    }
                );

                message.textContent = "Menu added successfully.";
            }

            document.getElementById("menu-form").reset();

            document
                .querySelector("#menu-form button[type='submit']")
                .textContent = "Add Menu";

            loadAdminMenus(
                selectedRestaurantId,
                document
                    .getElementById("menu-restaurant-name")
                    .textContent.replace("Menus - ", "")
            );

        } catch (error) {
            message.textContent = error.message;

        } finally {
            submitButton.disabled = false;
        }
    });



    async function deleteMenu(id) {
    if (!confirm("Delete this menu and its items?")) {
        return;
    }

    try {
        await apiRequest(`/menus/${id}`, {
            method: "DELETE"
        });

        document.getElementById("menu-message").textContent =
            "Menu deleted successfully.";

        const restaurantName =
            document
                .getElementById("menu-restaurant-name")
                .textContent.replace("Menus - ", "");

        loadAdminMenus(
            selectedRestaurantId,
            restaurantName
        );

    } catch (error) {
        document.getElementById("menu-message").textContent =
            error.message;
    }
}



let selectedMenuId = null;
let editingItemId = null;


async function loadAdminItems(menuId, menuName) {
    selectedMenuId = menuId;
    editingItemId = null;

    const section =
        document.getElementById("item-management");

    const container =
        document.getElementById("admin-items");

    section.hidden = false;

    document.getElementById("item-menu-name").textContent =
        `Menu Items - ${menuName}`;

    document.getElementById("item-form").reset();
    document.getElementById("cancel-item-edit").hidden = true;
    document
        .querySelector("#item-form button[type='submit']")
        .textContent = "Add Item";

    try {
        const response = await apiRequest(
            `/menus/${menuId}/items`
        );

        const items = response.data;

        container.innerHTML = "";

        if (items.length === 0) {
            container.textContent = "No items available.";
            return;
        }

        items.forEach((item) => {
            const box = document.createElement("div");

            const name = document.createElement("h4");
            name.textContent = item.name;

            const description = document.createElement("p");
            description.textContent =
                item.description || "No description.";

            const price = document.createElement("p");
            price.textContent =
                `$${Number(item.price).toFixed(2)}`;

            const editButton =
                document.createElement("button");

            editButton.textContent = "Edit";

            editButton.addEventListener("click", () => {
                editingItemId = item.id;

                document.getElementById("item-name").value =
                    item.name;

                document.getElementById("item-description").value =
                    item.description || "";

                document.getElementById("item-price").value =
                    item.price;

                document.getElementById("item-image").value =
                    item.image || "";

                document
                    .querySelector(
                        "#item-form button[type='submit']"
                    )
                    .textContent = "Update Item";
                
                document.getElementById("cancel-item-edit").hidden = false;
            });

            const deleteButton =
                document.createElement("button");

            deleteButton.textContent = "Delete";

            deleteButton.addEventListener("click", () => {
                deleteMenuItem(item.id);
            });

            box.appendChild(name);
            box.appendChild(description);
            box.appendChild(price);
            box.appendChild(editButton);
            box.appendChild(deleteButton);

            container.appendChild(box);
        });

    } catch (error) {
        document.getElementById("item-message").textContent =
            error.message;
    }
}

document
    .getElementById("item-form")
    .addEventListener("submit", async (event) => {

        event.preventDefault();
        const submitButton =
            document.querySelector("#item-form button[type='submit']");
        const item = {
            name:
                document.getElementById("item-name").value.trim(),

            description:
                document
                    .getElementById("item-description")
                    .value.trim(),

            price:
                Number(
                    document.getElementById("item-price").value
                ),

            image:
                document.getElementById("item-image").value.trim()
                || null
        };

        const message =
            document.getElementById("item-message");
        
        if (!item.name) {
            message.textContent = "Item name is required.";
            return;
        }

        if (
            !Number.isFinite(item.price) ||
            item.price < 0
        ) {
            message.textContent =
                "Please enter a valid price.";
            return;
        }

        submitButton.disabled = true;
        try {
            if (editingItemId) {

                await apiRequest(
                    `/menu-items/${editingItemId}`,
                    {
                        method: "PUT",
                        body: JSON.stringify(item)
                    }
                );

                message.textContent =
                    "Item updated successfully.";

                editingItemId = null;

            } else {

                await apiRequest(
                    `/menus/${selectedMenuId}/items`,
                    {
                        method: "POST",
                        body: JSON.stringify(item)
                    }
                );

                message.textContent =
                    "Item added successfully.";
            }

            document.getElementById("item-form").reset();

            document
                .querySelector(
                    "#item-form button[type='submit']"
                )
                .textContent = "Add Item";

            const menuName =
                document
                    .getElementById("item-menu-name")
                    .textContent.replace("Menu Items - ", "");

            loadAdminItems(selectedMenuId, menuName);

        } catch (error) {
            message.textContent = error.message;

        } finally {
            submitButton.disabled = false;
        }
    });


    async function deleteMenuItem(id) {
    if (!confirm("Are you sure you want to delete this item?")) {
        return;
    }

    try {
        await apiRequest(`/menu-items/${id}`, {
            method: "DELETE"
        });

        document.getElementById("item-message").textContent =
            "Item deleted successfully.";

        const menuName =
            document
                .getElementById("item-menu-name")
                .textContent.replace("Menu Items - ", "");

        loadAdminItems(selectedMenuId, menuName);

    } catch (error) {
        document.getElementById("item-message").textContent =
            error.message;
    }
}


document
    .getElementById("cancel-menu-edit")
    .addEventListener("click", () => {

        editingMenuId = null;

        document.getElementById("menu-form").reset();

        document
            .querySelector("#menu-form button[type='submit']")
            .textContent = "Add Menu";

        document.getElementById("cancel-menu-edit").hidden = true;
    });



    document
        .getElementById("cancel-item-edit")
        .addEventListener("click", () => {

            editingItemId = null;

            document.getElementById("item-form").reset();

            document
                .querySelector("#item-form button[type='submit']")
                .textContent = "Add Item";

            document.getElementById("cancel-item-edit").hidden = true;
        });


loadAdminCategories();
checkAdmin();