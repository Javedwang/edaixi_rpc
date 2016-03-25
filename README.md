![mahua](https://camo.githubusercontent.com/a4c71035cca1c7fdc7291211df4b7a1042ebf60b/687474703a2f2f63646e376f70656e2e6564616978692e636f6d2f6170705f7265736f75726365732f696f732f7563656e7465722f6176617461722e706e67)
## edaixi_rpc Client

##使用方法

### 1、引入包

```
composer require javedwang/edaixi_rpc
```
### 2、创建rpcmodels目录

### 3、在rpcmodels目录创建类如
```php
<?php
namespace App\Rpc\Model;
use  App\Lib\Rpc\Apimodel  as  Apimodel;

class RpcClient extends Apimodel
{
    const host = 'http://127.0.0.1:8888/api/v1/zombie';
	const api_url = '/client';
	const structure_url  = '/zombie_model_structure.json';
	public static $structure = null;
	public static $chain_methods = [];
	// http://localhost:80/v1/zombie/users/zombie_model_structure.json
	public function __construct()
	{
		parent::__construct();
	}
}

```
### 4、配置自己的项目composer.json自动加载rpcmodels目录
```javascript
"autoload": {
  "psr-4": {"App\\Rpc\\Model\\":["rpcmodels/"]}
}
```
### 5、codeigniter控制器中使用
```php
<?php
use  App\Rpc\Model as RpcModel;
class Welcome extends CI_Controller
{
    public function __construct(){
        parent::__construct();
    }

    public function index(){
        RpcModel\RpcClient::init();
        $c = RpcModel\RpcClient::rbool();
        var_dump($c);
        //TODO 待续
    }
}
```