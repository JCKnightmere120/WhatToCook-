<?php
namespace Database\Seeders;
use App\Models\IngredientCatalog;
use App\Models\Ingredient;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
class IngredientCatalogSeeder extends Seeder { public function run(): void { $items = [['rice',['brown rice','white rice'],'grains',['kg','g','cup']],['milk',[],'dairy',['litre','ml']],['eggs',['egg'],'dairy',['pieces']],['chicken',[],'meat',['kg','g']],['tomatoes',['tomato'],'produce',['pieces','g']],['carrots',['carrot'],'produce',['pieces','g']],['bihon noodles',['bihon'],'noodles',['g','packs']],['canned sardines',['sardines','sardine'],'canned goods',['cans']],['coconut milk',[],'canned goods',['cans','ml']],['tuna',[],'canned goods',['cans']],['salt',[],'seasoning',['g','tsp']],['yogurt',[],'dairy',['cup','g']],['pechay',[],'produce',['bundle']]]; foreach ($items as [$name,$aliases,$category,$units]) IngredientCatalog::updateOrCreate(['canonical_name'=>$name], ['aliases'=>$aliases,'category'=>$category,'is_approved'=>true,'default_units'=>$units]); Ingredient::select('name','unit')->distinct()->get()->each(function ($ingredient) { $name=Str::lower(trim($ingredient->name)); if ($name !== '') IngredientCatalog::firstOrCreate(['canonical_name'=>$name], ['aliases'=>[],'category'=>'recipe ingredient','is_approved'=>true,'default_units'=>array_values(array_filter([$ingredient->unit]))]); }); } }
