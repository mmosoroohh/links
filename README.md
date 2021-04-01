# Links
 Here is a simple Laravel web application done for the purpose of learning php unit testing.
 
### Usage
 - Authentication
    - Registration
    - Login
    - Forgot Password
    - Reset Password
    - Email Verification
    
- Links
    - View all links
    - Create links


### Prerequisites
 - PHP 8.0.3 
 - Laravel 7*
 - MySQL

### Installation
Download / clone the project to your local computer by:

    - Download the zip file of this repository.
    - Unzip it and navigate into the UI directory.

<pre><code>$ links </code></pre>

### Alternatively
Run the following command:

<pre><code>$ https://github.com/mmosoroohh/links.git</code></pre>

Locate Links folder in your local computer.

<pre><code>$ cd Links </code></pre>

Running Composer to install the dependencies for the projects

<pre><code> $ composer install </code></pre>


### Testing
Add the following line on `phpunit.xml` to configuration database for unit testing 
```
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
```

To run unit test 
<pre><code> php artisan test </code></pre>


### Authors
Arnold M. Osoro - [mmosoroohh](https://github.com/mmosoroohh)
