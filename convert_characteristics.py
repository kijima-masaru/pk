import json
import re

# characteristics.jsonを読み込み
with open('src/public/json/basic_deta/characteristics.json', 'r', encoding='utf-8') as f:
    characteristics = json.load(f)

# 特性名とIDのマッピングを作成
name_to_id = {}
for char in characteristics:
    name_to_id[char['name']] = char['id']

print(f"特性マッピング作成完了: {len(name_to_id)}個の特性")

# pokemon_megas.jsonを読み込み
with open('src/public/json/basic_deta/pokemons/pokemon_megas.json', 'r', encoding='utf-8') as f:
    pokemon_megas = json.load(f)

# pokemon_megas.jsonのcharacteristicsを数値に置換
converted_megas = 0
for pokemon in pokemon_megas:
    for i in range(1, 5):  # characteristics1_id から characteristics4_id まで
        char_key = f'characteristics{i}_id'
        if char_key in pokemon and pokemon[char_key] is not None:
            char_value = pokemon[char_key]
            # 文字列の場合（特性名）、数値に変換
            if isinstance(char_value, str) and not char_value.isdigit():
                if char_value in name_to_id:
                    pokemon[char_key] = name_to_id[char_value]
                    converted_megas += 1
                else:
                    print(f"警告: 特性名 '{char_value}' が見つかりません")

# pokemon_forms.jsonを読み込み
with open('src/public/json/basic_deta/pokemons/pokemon_forms.json', 'r', encoding='utf-8') as f:
    pokemon_forms = json.load(f)

# pokemon_forms.jsonのcharacteristicsを数値に置換
converted_forms = 0
for pokemon in pokemon_forms:
    for i in range(1, 5):  # characteristics1_id から characteristics4_id まで
        char_key = f'characteristics{i}_id'
        if char_key in pokemon and pokemon[char_key] is not None:
            char_value = pokemon[char_key]
            # 文字列の場合（特性名）、数値に変換
            if isinstance(char_value, str) and not char_value.isdigit():
                if char_value in name_to_id:
                    pokemon[char_key] = name_to_id[char_value]
                    converted_forms += 1
                else:
                    print(f"警告: 特性名 '{char_value}' が見つかりません")

# 変換されたファイルを保存
with open('src/public/json/basic_deta/pokemons/pokemon_megas.json', 'w', encoding='utf-8') as f:
    json.dump(pokemon_megas, f, ensure_ascii=False, indent=2)

with open('src/public/json/basic_deta/pokemons/pokemon_forms.json', 'w', encoding='utf-8') as f:
    json.dump(pokemon_forms, f, ensure_ascii=False, indent=2)

print(f"変換完了:")
print(f"pokemon_megas.json: {converted_megas}個の特性を数値に変換")
print(f"pokemon_forms.json: {converted_forms}個の特性を数値に変換")
