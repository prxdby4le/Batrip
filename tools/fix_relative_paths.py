#!/usr/bin/env python3
"""
Script para corrigir caminhos relativos incorretos em includes.
Corrige includes que usam 'includes/...' sem o caminho relativo correto.
"""

import os
import re
from pathlib import Path

def fix_relative_includes(file_path, project_root):
    """Corrige includes relativos em um arquivo"""
    try:
        with open(file_path, 'r', encoding='utf-8', errors='ignore') as f:
            content = f.read()
    except Exception as e:
        print(f"Erro ao ler {file_path}: {e}")
        return False
    
    original_content = content
    relative_file = Path(file_path).relative_to(project_root)
    file_dir = relative_file.parent
    
    # Calcula quantos níveis acima precisa ir para chegar à raiz
    depth = len(file_dir.parts)
    if depth == 0:
        prefix = '../'
    else:
        prefix = '../' * depth
    
    # Padrão para encontrar includes com 'includes/...'
    pattern = r"(include|require|include_once|require_once)\s+['\"]includes/([^'\"]+)['\"]"
    
    def replace_include(match):
        include_type = match.group(1)
        include_file = match.group(2)
        return f"{include_type} '{prefix}includes/{include_file}'"
    
    content = re.sub(pattern, replace_include, content)
    
    if content != original_content:
        try:
            with open(file_path, 'w', encoding='utf-8') as f:
                f.write(content)
            return True
        except Exception as e:
            print(f"Erro ao escrever {file_path}: {e}")
            return False
    
    return False

def main():
    project_root = Path(__file__).parent.parent
    print("=" * 60)
    print("Correção de Caminhos Relativos - Projeto Batrip")
    print("=" * 60)
    
    php_files = []
    for root, dirs, files in os.walk(project_root):
        dirs[:] = [d for d in dirs if not d.startswith('.')]
        for file in files:
            if file.endswith('.php'):
                php_files.append(Path(root) / file)
    
    fixed_count = 0
    for php_file in php_files:
        if fix_relative_includes(php_file, project_root):
            fixed_count += 1
            print(f"  Corrigido: {php_file.relative_to(project_root)}")
    
    print(f"\nTotal de arquivos corrigidos: {fixed_count}")
    print("Concluído!")

if __name__ == '__main__':
    main()

