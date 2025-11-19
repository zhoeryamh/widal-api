# Widal API

## Bahasa Indonesia
Proyek ini dibuat hanya untuk menghilangkan rasa penasaran saya sendiri untuk membuat alat penerjeman dari kalimat menjadi sandi orang tipar dan sebaliknya.
Jika ada galat atau sejenisnya, silahkan untuk membuka tiket.

Terima Kasih

## Basa Sunda
Proyék ieu didamel ngan saukur pikeun nyugemakeun rasa panasaran kuring ngeunaan nyiptakeun alat tarjamahan pikeun kalimah kana kode urang tipar sareng sabalikna.
Upami anjeun mendakan kasalahan atanapi masalah anu sami, mangga buka tikét.

Hatur nuhun.

## Widal non-reversal
Dlomén nyinyeu pipayer nyak ganyunul dineuk nguseyaneuk laga dakagalak nuliny nyeukanyak ngidwaneuk nyaraw walcayabak dineuk nariyab naka nope nyulany widal galeny gaharinka.
Nyudayi nyakceuk yekpanak nagarabak nyawakadi yagarab nyaku gayi, yanysa huna winéw.

Bawul kubuk.

## Widal reversal for API Decoding
Dlomén nyinyeu pipayer (ny)ak ganyunul dineuk (ng)useyaneuk laga dakagalak nuli(ny) (ny)eukanyak (ng)idwaneuk nyaraw walcayabak dineuk nariyab naka nope nyula(ny) widal gale(ny) gaharinka.
Nyudayi nyakceuk yekpanak nagarabak nyawakadi yagarab nyaku gayi, ya(ny)sa huna winéw.

bawul kubuk.

# GET Request Example

## To Widal (Encode without Reversal)
```
mode=to_widal&text=lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus sodales est nec vestibulum posuere. Integer sodales ullamcorper sem, ac ultricies nunc congue nec. Praesent ac mollis arcu.
```

## To Widal (Encode with Reversal)
```
mode=to_widal&text=lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus sodales est nec vestibulum posuere. Integer sodales ullamcorper sem, ac ultricies nunc congue nec. Praesent ac mollis arcu.&reversal=1
```

## From Widal (Decode)
```
mode=from_widal&text=roley nyidguy porol giw nyayew, jokgejwewul nyapidigji(ny) nyeriw. dbagerrug gopareg nyegw kej degwihuruy dogunyele. nyikwesel gopareg nyurrayjoldel gey, nyaj nyurwlijinyeg kukj jo(ny)unye kej. dlanyegekw nyaj yorrig nyalju.
```

# Response Example

## To Widal (Encode without Reversal)
```json
{
    "result": "roley nyidguy porol giw nyayew, jokgejwewul nyapidigjiny nyeriw. dbagerrug gopareg nyegw kej degwihuruy dogunyele. nyikwesel gopareg nyurrayjoldel gey, nyaj nyurwlijinyeg kukj jonyunye kej. dlanyegekw nyaj yorrig nyalju.",
    "mode": "to_widal"
}
```

## To Widal (Encode with Reversal)
```json
{
    "result": "roley nyidguy porol giw nyayew, jokgejwewul nyapidigji(ny) nyeriw. dbagerrug gopareg nyegw kej degwihuruy dogunyele. nyikwesel gopareg nyurrayjoldel gey, nyaj nyurwlijinyeg kukj jo(ny)unye kej. dlanyegekw nyaj yorrig nyalju.",
    "mode": "to_widal"
}
```

## From Widal (Decode)
```json
{
    "result": "lorem ipsum dolor sit amet, consectetur adipiscing elit. phasellus sodales est nec pestibulum posuere. integer sodales ullamcorper sem, ac ultricies nunc congue nec. praesent ac mollis arcu.",
    "mode": "from_widal"
}
```

# Deployment

## Laravel
1. Clone repository
```bash
git clone https://github.com/yourusername/widal-api.git
```
2. Install dependencies
```bash
composer install
```
3. Run server
```bash
php artisan serve
```