# Online Order

## Usage

-   `git clone https://github.com/NasrinRp/Shopping.git`
-   `composer install`
-   `php artisan jwt:secret`
-   `php artisan migrate`

## Set Env
set 
DB_CONNECTION=mongodb and 
DB_DATABASE=you_db_name

    
## Endpoints
    * User
        1 ) Register -> POST: /api/auth/register
	Body example : {
		name: testName,
		email: test@gmail.com,
		password: testPass
	}
        2 ) Login -> POST: /api/auth/login
	Body example : {
		email: test@gmail.com,
		password: testPass
	}
        3 ) Check Login -> GET: /api/auth
        4 ) Logout -> POST: /api/auth/logout
        
    2. Product
        1 ) Create -> POST: /api/products 
	Body example : {
		name: testName,
		price: 100,
		inventory: 100
	}
        2 ) Update -> PUT: /api/products/{id} 
        3 ) Delete -> DELETE: /api/products/{id}
        4 ) Show -> GET: /api/products/{id}
        5 ) All -> GET: /api/products
        
    3. Order
        1 ) Create -> POST: /api/orders 
	Body example : {
		orderItems: {
			upsert: [
				[product_id: id, count: 1],
				[product_id: id2, count: 1],
			]
		}
	}
        2 ) Update -> PUT: /api/orders/{id}
	Body example : {
		orderItems: {
			upsert: [
				[id: orderItemId, product_id: id, count: 1],
				[product_id: id2, count: 1],
			]
			delete: [orderItemId]
		}
	}
        3 ) Cancel -> PUT: /api/orders/{id}/cancel
	4 ) Show -> GET: /api/orders/{id}
        5 ) All -> GET: /api/orders
        

## Thanks
