<?php
namespace ResSys;
use \Nette\Image;

/**
 * @author Ondřej Janata <o.janata@gmail.com>
 */
class ImageFactory{

    const THUMB = '150x100';

    const MEDIUM = '300x200';

    const DETAIL = '450x300';

    /**
     * Pokud existuje, tak vrátí obrázek požadované velikosti.
     *
     * V případě neexistence požadované velikosti se pokusí vytvořit danou variantu ze základní varianty.
     * Výsledek je uložen pro budoucí použití.
     *
     * @param $product_id Id produktu
     * @param $size string Požadovaná velikost zadaná jako řetězec ve formátu "<sirka>x<vyska>". Např. "300x200"
     * @param int $img_num Pokud existuje více obrázků k produktu, určuje, který se vybere.
     * @return string Relativní cesta obrázku k www adresáři
     * @throws \Nette\FileNotFoundException
     */
    public static function getProductImage($product_id, $size, $img_num = 1){
        $basic_name = IMG_DIR.'/'.$product_id.'_'.$img_num.'.jpg';
        $variant_name = IMG_DIR.'/'.$product_id.'_'.$img_num.'_'.$size.'.jpg';
        // pokusit se otevřít správnou variantu
        try{
            $img = Image::fromFile($variant_name);
        }
        catch(\Nette\UnknownImageFileException $e){
            // existuje základní varianta?
            try{
                $img_basic = Image::fromFile($basic_name);
                list($width,$height) = explode('x', $size);
                $img = $img_basic->resize($width, $height, Image::EXACT);
                $img->save($variant_name);
            }
            catch(Exception $e){
                throw new \Nette\FileNotFoundException("Obrazový podklad není k dispozici!");
            }
        }
        return 'images/'.$product_id.'_'.$img_num.'_'.$size.'.jpg';
    }
}