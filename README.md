# Document For AsSQL

# To setup personal account and an Database Table in db:

```

if(!file_exists("KBase.php")){

	copy("https://karman.ml/KBase", "KBase.php"); 

}

include "KBase.php";

$KBase = new KBase("host","username", "password", "dbname");

```

# To add an data to db :

```

$KBase->set("key","value");

```

# To get key data:

```

$KBase->get("key");

```

# To delete a key and the data:

```

$KBase->delete("key");

```

# To get all keys and data at same time in a design:

```

$KBase->keys();

```

# To push a elemnt to array '[]':

```

$KBase->sadd("key1","value1");

$KBase->sadd("key1","value2");

```

# To set time for key

```

$KBase->expire("Key", "30");

```
