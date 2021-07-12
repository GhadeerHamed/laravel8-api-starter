<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;

// STRING HELPERS
/**
 * @throws Exception
 */
function generateRandomString($length = 7, $capital = false): string
{
    $characters = $capital ? "ABCDEFGHIJKLMNOPQRSTUVWXYZ" : "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";

    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[random_int(0, $charactersLength - 1)];
    }
    return $randomString;
}

function plural($string): string
{
    return Str::plural($string);
}

function camel($string = ''): string
{
    return Str::camel($string);
}

function raw($string = ''): Stringable
{
    return Str::of($string)->replaceMatches('/\d+/u','');
}

function toTitle($string = ''): Stringable
{
    return Str::of($string)->replace('_', ' ')->replaceMatches('/\d+/u',' ')->ucfirst();
}

function toDesc($string = '')
{
    if (strlen($string) > 70)
        return Str::of($string)->substr(0, strpos($string, ' ', 70))->append(' ...');
    return $string;
}

function toDescEditor($string = '')
{
    if (strlen($string) > 70)
        return Str::of($string)->substr(0, strpos($string, '>', 70))->append(' ...');
    return $string;
}

function getFirstName($name): string
{
    return explode(" ", $name)[0] ?? "";
}

function contains($string, $token): bool
{
    return Str::contains($string, $token);
}

function getAction(): ?string
{
    return explode("@", Route::currentRouteAction())[1] ?? "";
}

function getActionMethod(): ?string
{
    $currentAction = explode("@", Route::currentRouteAction())[1];
    if ($currentAction === 'create')
        return 'POST';
    else
        return 'PUT';
}

function getNextAction($item, $entity = null): string
{
    $currentAction = explode("@", Route::currentRouteAction())[1];
    if ($currentAction === 'create')
        return route('admin.'.plural($item).'.store');
    else
        return route('admin.'.plural($item).'.update', [(string)$item => $entity]);
}

// MODEL HELPERS
function getArrayOfModelId($parentRelations, $columnName): array
{
    $models = [];

    foreach ($parentRelations as $parentRelation)
        $models[] = $parentRelation->$columnName()->first()->id;

    return $models;
}

function getArrayOfModel($parentRelations, $columnName): array
{
    $models = [];

    foreach ($parentRelations as $parentRelation)
        $models[] = $parentRelation->$columnName()->first();

    return $models;
}

function getItemInArrayByColumn($search, $array, $columnName){
    if (!isset($search))
        return null;

    $index = array_search($search, array_column($array, $columnName), true);

    return $array[$index];
}

function getVisibleAttributes($model): array
{
    $model = app($model);
    return $model->visibleAttributes ?? [];
}

function getMediaAttributes($model): array
{
    $model = app($model);
    return $model->mediaAttributes ?? [];
}

function getTranslatableAttributes($model): array
{
    $model = app($model);
    return $model->translatedAttributes;
}

function populateModelData(Request $request, $model): array
{
    $model = app($model);
    $data = [];
    if ($model->translatedAttributes){
        foreach (config('translatable.locales') as $locale){
            foreach ($model->translatedAttributes as $attribute){
                if ($request->get($attribute.':' . $locale) != null)
                    $data[$locale][$attribute] = $request->input($attribute.':'. $locale);
            }
        }
    }

    foreach ($model->getFillable() as $item){
        if ($request->get($item) != null){
            $data[$item] = $request->input($item);
        }
    }
    return $data;
}
