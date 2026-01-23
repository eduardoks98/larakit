<?php

namespace Eduardoks98\BaseApi\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller;
use Eduardoks98\BaseApi\Traits\HasApiResponses;

class ApiController extends Controller
{
    use AuthorizesRequests, ValidatesRequests, HasApiResponses;
}
