LaBloom
=======

Whats new
- Added FNVhash hash class implementation, use PHP extansion if posible by environment.
- FNVhash is know default for hashing values
- FNVhash is only 32 byte, so data length fo filter is limited to around 30k different values.

Bloom filter, sadly its not very accurate, becouse PHP doesnt have suitable hash funtions, which would generate uniformly distributed values. 
For now its good to test up to some thousands of values then using long bloom filter.
For what is bloom filter and why its so cool, check wiki please :)
LaBloom class provides containers for filters, name and filter length is provided then creating filter.
Filter length should be set appropriate to data set size.
```php
$filter = LaBloom::make("my_awesome_filter", 1024);

// later in code, we will whant to work with same filter
$f = LaBloom::get("my_awesome_filter");
```
###Easy to add some values to filter, note that filter works on string data, so you would like to json or serialize it before feeding to filter :)
```php
$filter->add("test value 1");
$filter->add("test value 2");
// feed array of strings to add more data with one call
$filter->add(array("test value 3", "more test values"));
```
###To check posible value existence in set
```php
$filter->has("no such value"); // returns true or false
// you can do array too
$filter->has(array('val1', 'val2', 'val3'));
```
Keep in mind that usingin array check values will be replaced by existence values, but array keys will pe preserved.
###fail_rate function returns a probability for falsy checks, this should be low, lower then 1. If its higher, you might whant to use longer filter.
```php
$filter->fail_rate();
```
### You can use save and load functions to save filter values, and to load them later
Only values is saved and loaded. Load function will dump all current filter values, and set filter length to amount of values passed to function.
```php
$filter->save(); // outputs a string something like 1,0,1,,0,1,1,1,0,1,1,0
$filter->load("1,0,1,,0,1,1,1,0,1,1,0"); // load string to filter
```

### Hash functions used for filter is set in public array parameter hash_f, there is array hash_c for custom hash functions, thes are experemental, might hack around if you don't get good results from filter.
- hash_f - array of PHP's native hash function names, default array("sha1", "adler32", "crc32");
- hash_c - should be array of callbacks, callable function should accept single value parameter.