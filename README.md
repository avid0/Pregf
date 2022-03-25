# Pregf
Range array maker and random string generator functions by regular patterns.

## preg_rand
#### Example 1:
Random password generator
```php
print preg_rand("/\w{16}/");
```
Result: Q7U9K2AuPh3470Z9
#### Example 2:
Random tg bot api token generator
```php
print preg_rand("/[0-9]{4,16}:AA[GFHE][a-zA-Z0-9-_]{32}/");
```
Result: 103854368132:AAFoHMXJMYVbCzGA_6dxNY3lHAEf1s33miw
## preg_range
#### Example 1:
List of alphabets:
```php
print_r(preg_range("/[a-zA-Z]/"));
```
Result:
Array
(
    [0] => a
    [1] => b
...
    [50] => Y
    [51] => Z
)
### Example 2:
```php
print_r(preg_range("/a:[01]/i"));
```
Result:
Array
(
    [0] => a:0
    [1] => a:1
    [2] => A:0
    [3] => A:1
)
