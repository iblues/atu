###如果使用telescope报错:Target class [env] does not exist.

请修改App\Providers\TelescopeServiceProvider文件
```

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        ....
        Telescope::filter(function (IncomingEntry $entry) {
            +++
            if(App::runningUnitTests()){
                return true;
            }
            ....
        });
    }


    protected function hideSensitiveRequestDetails()
    {
        +++++
        if(App::runningUnitTests()){
            return true;
        }
        .....
     }
```