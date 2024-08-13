# SQL
I've been on a hunt to find a way to convert old mysql_* functions into PDO statements, while maintaining security efforts and ensure all the variables are binded correctly, and since theres so many different variables of SQL statements, and how in dept they can go it was starting to get tricky.
I also had to keep in mind that most of my SQL statements had different variable types within the statement itself.

these could be found before and within the = mysql_query()
```php
$var
$var['array']
$var['array']['levels']
$this->var
$this->var['array']
$this->var['array']['levels']
```
And then when one of those variables are within the mysql_query(PARAMETER) they could look like the following:
```php
// the $var below, could look like any of the 6 var examples above, but for simplicity, I'm using $var
'.$var.'
`'.$var.'`
".$var."
`".$var."` // this is that ` next to the 1 key
```
And then we need to ensure when theres conditions within the SQL Statement like `WHERE col IN('".$var1."', '".$var2."')` we would need to make sure the ) before and after the IN condition does not break the finder.
