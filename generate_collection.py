import json

base_url = "{{base_url}}"
# Standard Laravel API prefix is /api, and routes/api.php has prefix 'v1'
# So routes are /api/v1/...
api_prefix = ["api", "v1"] 

# Helper
def get_url(path_segments, query=None):
    # path_segments should be relative to api/v1 if inside the group, or we just prepend api/v1
    # Actually, let's just make the caller provide the full path segments after base_url
    full_path = api_prefix + path_segments
    url = {
        "raw": f"{base_url}/{'/'.join(full_path)}",
        "host": ["{{base_url}}"],
        "path": full_path
    }
    if query:
        q_params = []
        url["raw"] += "?"
        for k, v in query.items():
            q_params.append({"key": k, "value": v})
            url["raw"] += f"{k}={v}&"
        url["raw"] = url["raw"].rstrip("&") # remove trailing &
        url["query"] = q_params
    return url

items = []

# Auth
items.append({
    "name": "Auth",
    "item": [
        {
            "name": "Login",
            "request": {
                "method": "POST",
                "header": [{"key": "Accept", "value": "application/json"}],
                "url": get_url(["login"]), # /api/v1/login
                "body": {
                    "mode": "raw",
                    "raw": json.dumps({
                        "email": "customer1@kremeya.com",
                        "password": "password",
                        "device_name": "postman",
                        # "phone": "" # Optional
                    }, indent=2),
                    "options": {"raw": {"language": "json"}}
                }
            }
        },
        {
            "name": "Logout",
            "request": {
                "method": "POST",
                "header": [{"key": "Accept", "value": "application/json"}],
                "url": get_url(["logout"]) # /api/v1/logout
            }
        },
         {
            "name": "Me",
            "request": {
                "method": "GET",
                "header": [{"key": "Accept", "value": "application/json"}],
                "url": get_url(["me"]) # /api/v1/me
            }
        }
    ]
})

# Customers
items.append({
    "name": "Customers",
    "item": [
        {
            "name": "List Customers",
            "request": {
                "method": "GET",
                "header": [{"key": "Accept", "value": "application/json"}],
                "url": get_url(["customers"], query={"page": "1", "per_page": "15", "search": ""})
            }
        },
        {
            "name": "Create Customer",
            "request": {
                "method": "POST",
                "header": [{"key": "Accept", "value": "application/json"}],
                "url": get_url(["customers"]),
                "body": {
                    "mode": "raw",
                    "raw": json.dumps({
                        "name": "New Customer",
                        "email": "newcustomer@example.com",
                        "phone": "0919999999",
                        "password": "password",
                        "password_confirmation": "password",
                        "city_id": 1,
                        "region_id": 1,
                        "gender": "male"
                    }, indent=2),
                    "options": {"raw": {"language": "json"}}
                }
            }
        }
    ]
})

# Employees
items.append({
    "name": "Employees",
    "item": [
        {
            "name": "List Employees",
            "request": {
                "method": "GET",
                "header": [{"key": "Accept", "value": "application/json"}],
                "url": get_url(["employees"], query={"page": "1"})
            }
        },
        {
            "name": "Create Employee",
            "request": {
                "method": "POST",
                "header": [{"key": "Accept", "value": "application/json"}],
                "url": get_url(["employees"]),
                "body": {
                    "mode": "raw",
                    "raw": json.dumps({
                        "name": "New Employee",
                        "email": "newemployee@example.com",
                        "phone": "0929999999",
                        "password": "password",
                        "salary": 2000.00
                    }, indent=2),
                    "options": {"raw": {"language": "json"}}
                }
            }
        }
    ]
})

# Products
items.append({
    "name": "Products",
    "item": [
        {
            "name": "List Products",
            "request": {
                "method": "GET",
                "header": [{"key": "Accept", "value": "application/json"}],
                "url": get_url(["products"], query={"page": "1", "search": "", "is_active": "1"})
            }
        },
        {
            "name": "Create Product",
            "request": {
                "method": "POST",
                "header": [{"key": "Accept", "value": "application/json"}],
                "url": get_url(["products"]),
                "body": {
                    "mode": "formdata",
                    "formdata": [
                        {"key": "name", "value": "Product Name", "type": "text"},
                        {"key": "description", "value": "Description...", "type": "text"},
                        {"key": "selling_price", "value": "150.00", "type": "text"},
                        {"key": "buying_price", "value": "100.00", "type": "text"},
                        {"key": "is_active", "value": "1", "type": "text"},
                        # Images handled manually in Postman usually, but we can add placeholders
                        {"key": "images[]", "type": "file", "src": [], "description": "Upload at least 1 image"} 
                    ]
                }
            }
        }
    ]
})

# Orders
items.append({
    "name": "Orders",
    "item": [
        {
            "name": "List Orders",
            "request": {
                "method": "GET",
                "header": [{"key": "Accept", "value": "application/json"}],
                "url": get_url(["orders"], query={"page": "1", "status": ""})
            }
        },
        {
            "name": "Create Order",
            "request": {
                "method": "POST",
                "header": [{"key": "Accept", "value": "application/json"}],
                "url": get_url(["orders"]),
                "body": {
                    "mode": "raw",
                    "raw": json.dumps({
                        "customer_id": 1,
                        "city_id": 1,
                        "region_id": 1,
                        "status": "pending",
                        "notes": "Urgent delivery"
                    }, indent=2),
                    "options": {"raw": {"language": "json"}}
                }
            }
        }
    ]
})

# Cart
items.append({
    "name": "Cart",
    "item": [
        {
            "name": "Get Customer Cart",
            "request": {
                "method": "GET",
                "header": [{"key": "Accept", "value": "application/json"}],
                # Route: Route::get('/customers/{customer}/cart', [CartController::class, 'show']); (Assumed based on controller docblock which said /v1/customers/{customer}/cart)
                # If routes/api.php adheres to this:
                "url": get_url(["customers", ":customerId", "cart"]),
                "variable": [{"key": "customerId", "value": "1"}]
            }
        },
        {
            "name": "Add Items to Cart",
            "request": {
                "method": "POST",
                "header": [{"key": "Accept", "value": "application/json"}],
                # Route: Route::post('/customers/{customer}/cart/items', [CartController::class, 'addItems']);
                "url": get_url(["customers", ":customerId", "cart", "items"]),
                "body": {
                    "mode": "raw",
                    "raw": json.dumps({
                        "items": [
                            {"product_id": 1, "quantity": 2},
                            {"product_id": 2, "quantity": 1}
                        ]
                    }, indent=2),
                    "options": {"raw": {"language": "json"}}
                },
                 "variable": [{"key": "customerId", "value": "1"}]
            }
        },
        {
            "name": "Clear Cart",
            "request": {
                "method": "DELETE",
                "header": [{"key": "Accept", "value": "application/json"}],
                # Route: Route::delete('/customers/{customer}/cart', [CartController::class, 'clear']);
                "url": get_url(["customers", ":customerId", "cart"]),
                "variable": [{"key": "customerId", "value": "1"}]
            }
        }
    ]
})


collection = {
    "info": {
        "name": "Kremeya Updated",
        "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
    },
    "item": items,
    "variable": [
        {
            "key": "base_url",
            "value": "http://127.0.0.1:8000",
            "type": "string"
        }
    ]
}

print(json.dumps(collection, indent=2))
