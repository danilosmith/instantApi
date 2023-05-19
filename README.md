# InstantAPI

InstantAPI is a lightweight PHP API that allows you to connect to any database without the need to write custom endpoints. It aims to be easy to install and use, making it suitable for creating simple applications.

## Installation

1. Download the `InstantApi.php` file.
2. Place the file on your server.
3. Configure the database settings in the `$config` array inside the file.
4. Connect the API to your database.

## Usage

### Endpoints

- **GET /app/api**: Retrieves a list of available tables in the connected database.

- **GET /app/api/{table_name}**: Retrieves records from the specified `{table_name}`. You can optionally use query parameters to limit and skip records.

- **POST /app/api/{table_name}**: Adds a new record to the specified `{table_name}`.

- **GET /app/api/{table_name}/{id}**: Retrieves a specific record from the specified `{table_name}` and `{id}`.

- **PUT /app/api/{table_name}/{id}**: Updates a specific record in the specified `{table_name}` and `{id}`.

- **DELETE /app/api/{table_name}/{id}**: Deletes a specific record from the specified `{table_name}` and `{id}`.

### Examples

Assuming the base URL of the API is `http://yourdomain.com/app/api`, here are some example requests:

- Retrieve all tables: GET http://yourdomain.com/app/api

- Retrieve all records from a table: GET http://yourdomain.com/app/api/{table_name}

- Retrieve a specific record from a table: GET http://yourdomain.com/app/api/{table_name}/{id}

- Add a new record to a table: POST http://yourdomain.com/app/api/{table_name}

- Update a specific record in a table: PUT http://yourdomain.com/app/api/{table_name}/{id}

- Delete a specific record from a table: DELETE http://yourdomain.com/app/api/{table_name}/{id}

## Requirements

- PHP 5.6 or higher.
- PDO extension enabled.

## License

This project is licensed under the [GPL-3.0 License](LICENSE).
