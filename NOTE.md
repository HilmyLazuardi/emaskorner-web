## Laravel Validation
- 'required' => ':attribute ' . lang('should not be empty', $this->translations),
- 'unique' => ':attribute ' . lang('has already been taken, please input another data', $this->translations),
- 'integer' => ':attribute ' . lang('must be an integer', $this->translations),
- 'numeric' => ':attribute ' . lang('must be a number', $this->translations),
- 'email' => ':attribute ' . lang('must be a valid email address', $this->translations),
- 'image' => ':attribute ' . lang('must be an image', $this->translations),
- 'max' => ':attribute ' . lang('may not be greater than #item', $this->translations, ['#item' => '2MB']),
- 'min' => ':attribute ' . lang('must be at least #item characters', $this->translations, ['#item' => '8']),
- 'confirmed' => ':attribute ' . lang('confirmation does not match', $this->translations),
- 'regex' => ':attribute ' . lang('format is invalid', $this->translations),


## Session Dictionary
- sysadmin : store authenticated admin data 
- redirect_uri_admin : store redirect uri for admin panel
- language_used : store language used
- country_used : store country used
- sio_countries : store available countries
- sio_languages : store available languages
- sio_translations : store available translations
- user_ip_address : store user's IP address
