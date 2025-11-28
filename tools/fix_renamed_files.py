#!/usr/bin/env python3
"""
Corrige todas as referências aos arquivos renomeados em app/
"""

import os
import re
from pathlib import Path

class RenamedFilesFixer:
    def __init__(self, project_root):
        self.project_root = Path(project_root).resolve()
        self.renamed_files = {}  # lowercase -> actual
        self.fixes = []
        
    def build_renamed_map(self):
        """Mapeia arquivos renomeados em app/"""
        print("📁 Mapeando arquivos renomeados em app/...")
        
        for root, dirs, files in os.walk(self.project_root / 'app'):
            dirs[:] = [d for d in dirs if not d.startswith('.')]
            
            root_path = Path(root)
            for file in files:
                if file.endswith('.php'):
                    file_path = root_path / file
                    try:
                        relative_path = file_path.relative_to(self.project_root)
                        # Cria mapeamento lowercase -> actual
                        normalized = str(relative_path).lower().replace('\\', '/')
                        actual = str(relative_path).replace('\\', '/')
                        self.renamed_files[normalized] = actual
                    except ValueError:
                        pass
        
        print(f"   ✓ {len(self.renamed_files)} arquivos mapeados")
    
    def find_correct_path(self, ref_path, current_file):
        """Encontra o caminho correto para uma referência"""
        # Limpa a referência
        ref_clean = ref_path
        if '__DIR__' in ref_path or 'ROOT_PATH' in ref_path:
            match = re.search(r"['\"]([^'\"]+)['\"]", ref_path)
            if match:
                ref_clean = match.group(1)
        
        if ref_clean.startswith('./'):
            ref_clean = ref_clean[2:]
        if ref_clean.startswith('/'):
            ref_clean = ref_clean[1:]
        
        # Se não começa com app/, não é nosso problema
        if not ref_clean.lower().startswith('app/'):
            return None
        
        ref_normalized = ref_clean.lower().replace('\\', '/')
        
        # Procura no mapa
        if ref_normalized in self.renamed_files:
            return self.renamed_files[ref_normalized]
        
        return None
    
    def fix_file(self, file_path):
        """Corrige referências em um arquivo"""
        try:
            with open(file_path, 'r', encoding='utf-8', errors='ignore') as f:
                content = f.read()
        except:
            return False
        
        original_content = content
        relative_file = file_path.relative_to(self.project_root)
        modified = False
        
        # Padrões para encontrar referências
        patterns = [
            # require/include com __DIR__ ou ROOT_PATH
            (r"(require|require_once|include|include_once)\s*\(\s*([^)]*['\"]([^'\"]+)['\"][^)]*)\)", 3),
            # require/include simples
            (r"(require|require_once|include|include_once)\s*\(\s*['\"]([^'\"]+)['\"]", 2),
            # require/include sem parênteses
            (r"(require|require_once|include|include_once)\s+['\"]([^'\"]+)['\"]", 2),
        ]
        
        for pattern, group_idx in patterns:
            def replace_match(match):
                nonlocal modified
                full_match = match.group(0)
                ref_path = match.group(group_idx)
                
                if ref_path.startswith('http://') or ref_path.startswith('https://'):
                    return full_match
                
                correct_path = self.find_correct_path(ref_path, file_path)
                
                if correct_path and correct_path != ref_path:
                    # Mantém a estrutura original
                    if '__DIR__' in full_match or 'ROOT_PATH' in full_match:
                        if 'ROOT_PATH' in full_match:
                            new_path = f"ROOT_PATH . '/{correct_path}'"
                        else:
                            # Calcula caminho relativo
                            current_dir = Path(file_path).parent.relative_to(self.project_root)
                            try:
                                rel_path = os.path.relpath(
                                    self.project_root / correct_path,
                                    self.project_root / current_dir
                                ).replace('\\', '/')
                                if not rel_path.startswith('.'):
                                    rel_path = './' + rel_path
                                new_path = f"__DIR__ . '/{rel_path}'"
                            except:
                                new_path = f"__DIR__ . '/{correct_path}'"
                    else:
                        # Caminho relativo simples
                        current_dir = Path(file_path).parent.relative_to(self.project_root)
                        try:
                            rel_path = os.path.relpath(
                                self.project_root / correct_path,
                                self.project_root / current_dir
                            ).replace('\\', '/')
                            if not rel_path.startswith('.'):
                                rel_path = './' + rel_path
                            new_path = rel_path
                        except:
                            new_path = correct_path
                    
                    modified = True
                    self.fixes.append((str(relative_file), ref_path, new_path))
                    return full_match.replace(ref_path, new_path)
                
                return full_match
            
            content = re.sub(pattern, replace_match, content)
        
        if modified:
            try:
                with open(file_path, 'w', encoding='utf-8') as f:
                    f.write(content)
                return True
            except Exception as e:
                print(f"   ✗ Erro ao escrever {file_path}: {e}")
                return False
        
        return False
    
    def fix_all(self):
        """Corrige todos os arquivos"""
        print("\n🔧 Corrigindo referências...")
        
        php_files = []
        for root, dirs, files in os.walk(self.project_root):
            dirs[:] = [d for d in dirs if not d.startswith('.') and d not in ['vendor', 'node_modules']]
            for file in files:
                if file.endswith('.php'):
                    php_files.append(Path(root) / file)
        
        fixed_count = 0
        for php_file in php_files:
            if self.fix_file(php_file):
                fixed_count += 1
                print(f"   ✓ {php_file.relative_to(self.project_root)}")
        
        print(f"\n   ✓ {fixed_count} arquivos corrigidos")
        return fixed_count
    
    def run(self):
        """Executa correção"""
        print("=" * 70)
        print("CORREÇÃO DE REFERÊNCIAS A ARQUIVOS RENOMEADOS")
        print("=" * 70)
        
        self.build_renamed_map()
        self.fix_all()
        
        print("\n" + "=" * 70)
        print(f"Total de correções: {len(self.fixes)}")
        print("=" * 70)

def main():
    project_root = Path(__file__).parent.parent
    fixer = RenamedFilesFixer(project_root)
    fixer.run()

if __name__ == '__main__':
    main()

