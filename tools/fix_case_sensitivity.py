#!/usr/bin/env python3
"""
Script para corrigir inconsistências de case sensitivity em referências de arquivos PHP.
Analisa a estrutura real de arquivos e corrige todas as referências no código.
"""

import os
import re
from pathlib import Path
from collections import defaultdict

class CaseSensitivityFixer:
    def __init__(self, project_root):
        self.project_root = Path(project_root).resolve()
        self.file_map = {}  # Mapeia caminhos normalizados (lowercase) para caminhos reais
        self.fixes_applied = []
        
    def build_file_map(self):
        """Constrói um mapa de todos os arquivos reais do projeto"""
        print("Mapeando estrutura de arquivos...")
        
        for root, dirs, files in os.walk(self.project_root):
            # Ignora diretórios ocultos e git
            dirs[:] = [d for d in dirs if not d.startswith('.')]
            
            root_path = Path(root)
            for file in files:
                if file.endswith('.php'):
                    file_path = root_path / file
                    relative_path = file_path.relative_to(self.project_root)
                    
                    # Cria chave normalizada (lowercase) para busca case-insensitive
                    normalized = str(relative_path).lower().replace('\\', '/')
                    actual = str(relative_path).replace('\\', '/')
                    
                    if normalized in self.file_map:
                        # Se já existe, mantém o que já está mapeado (primeiro encontrado)
                        pass
                    else:
                        self.file_map[normalized] = actual
        
        print(f"Mapeados {len(self.file_map)} arquivos PHP")
    
    def find_file_reference(self, ref_path, current_file):
        """
        Encontra o arquivo real correspondente a uma referência.
        Retorna o caminho correto ou None se não encontrar.
        """
        # Remove variáveis como __DIR__ e ROOT_PATH do início
        ref_path_clean = ref_path
        if '__DIR__' in ref_path or 'ROOT_PATH' in ref_path:
            # Extrai apenas a parte do caminho após a concatenação
            # Ex: __DIR__ . '/../includes/head.php' -> '../includes/head.php'
            match = re.search(r"['\"]([^'\"]+)['\"]", ref_path)
            if match:
                ref_path_clean = match.group(1)
        
        # Normaliza o caminho da referência
        ref_normalized = ref_path_clean.lower().replace('\\', '/')
        
        # Se a referência começa com ./, remove
        if ref_normalized.startswith('./'):
            ref_normalized = ref_normalized[2:]
        
        # Remove barras iniciais
        if ref_normalized.startswith('/'):
            ref_normalized = ref_normalized[1:]
        
        # Tenta encontrar correspondência exata primeiro
        if ref_normalized in self.file_map:
            return self.file_map[ref_normalized]
        
        # Tenta encontrar correspondência parcial (pode ser caminho relativo)
        current_dir = Path(current_file).parent.relative_to(self.project_root)
        
        # Resolve caminho relativo
        if ref_path_clean.startswith('../') or ref_path_clean.startswith('./'):
            # Caminho relativo
            try:
                resolved = (current_dir / ref_path_clean).resolve().relative_to(self.project_root)
                resolved_normalized = str(resolved).lower().replace('\\', '/')
                if resolved_normalized in self.file_map:
                    return self.file_map[resolved_normalized]
            except:
                pass
        
        # Busca por nome de arquivo apenas
        filename = Path(ref_path_clean).name.lower()
        matches = []
        for normalized, actual in self.file_map.items():
            if normalized.endswith('/' + filename) or normalized == filename:
                matches.append(actual)
        
        # Se encontrou apenas uma correspondência, retorna
        if len(matches) == 1:
            return matches[0]
        # Se encontrou múltiplas, tenta escolher a mais próxima
        elif len(matches) > 1:
            # Prefere arquivos na mesma estrutura de diretórios
            current_parts = str(current_dir).lower().split('/')
            best_match = matches[0]
            best_score = 0
            for match in matches:
                match_parts = match.lower().split('/')
                # Conta quantas partes do caminho coincidem
                score = sum(1 for a, b in zip(current_parts, match_parts) if a == b)
                if score > best_score:
                    best_score = score
                    best_match = match
            return best_match
        
        return None
    
    def fix_file(self, file_path):
        """Corrige todas as referências em um arquivo PHP"""
        try:
            with open(file_path, 'r', encoding='utf-8', errors='ignore') as f:
                content = f.read()
        except Exception as e:
            print(f"Erro ao ler {file_path}: {e}")
            return False
        
        original_content = content
        relative_file = Path(file_path).relative_to(self.project_root)
        
        # Padrões para encontrar referências de arquivos
        patterns = [
            # require/include com __DIR__ ou ROOT_PATH e concatenação
            (r"(require|require_once|include|include_once)\s*\(\s*[^)]*['\"]([^'\"]+)['\"]", 2),
            # require/include com aspas simples (padrão simples)
            (r"(require|require_once|include|include_once)\s*\(\s*['\"]([^'\"]+)['\"]", 2),
            # require/include sem parênteses
            (r"(require|require_once|include|include_once)\s+['\"]([^'\"]+)['\"]", 2),
            # use statements (para namespaces)
            (r"use\s+([A-Za-z0-9\\_]+)", 1),
        ]
        
        modified = False
        
        for pattern, group_idx in patterns:
            def replace_match(match):
                nonlocal modified
                full_match = match.group(0)
                ref_path = match.group(group_idx)
                
                # Para use statements, verifica se é namespace do projeto
                if 'use' in full_match and 'App\\' in ref_path:
                    # Converte namespace para caminho de arquivo
                    namespace_path = ref_path.replace('App\\', '').replace('\\', '/') + '.php'
                    # Tenta encontrar no app/
                    app_path = 'app/' + namespace_path
                    app_normalized = app_path.lower()
                    if app_normalized in self.file_map:
                        correct_path = self.file_map[app_normalized]
                        # Converte de volta para namespace
                        correct_namespace = 'App\\' + correct_path.replace('app/', '').replace('/', '\\').replace('.php', '')
                        if correct_namespace != ref_path:
                            modified = True
                            return full_match.replace(ref_path, correct_namespace)
                    return full_match
                
                # Para require/include, encontra o arquivo real
                correct_path = self.find_file_reference(ref_path, file_path)
                if correct_path and correct_path != ref_path:
                    # Mantém o tipo de aspas original
                    quote = "'" if "'" in match.group(0) else '"'
                    modified = True
                    return full_match.replace(ref_path, correct_path)
                
                return full_match
            
            content = re.sub(pattern, replace_match, content)
        
        if modified:
            try:
                with open(file_path, 'w', encoding='utf-8') as f:
                    f.write(content)
                self.fixes_applied.append(str(relative_file))
                return True
            except Exception as e:
                print(f"Erro ao escrever {file_path}: {e}")
                return False
        
        return False
    
    def fix_all(self):
        """Corrige todos os arquivos PHP do projeto"""
        print("\nCorrigindo referências...")
        
        php_files = []
        for root, dirs, files in os.walk(self.project_root):
            dirs[:] = [d for d in dirs if not d.startswith('.')]
            for file in files:
                if file.endswith('.php'):
                    php_files.append(Path(root) / file)
        
        fixed_count = 0
        for php_file in php_files:
            if self.fix_file(php_file):
                fixed_count += 1
                print(f"  Corrigido: {php_file.relative_to(self.project_root)}")
        
        print(f"\nTotal de arquivos corrigidos: {fixed_count}")
        return fixed_count

def main():
    project_root = Path(__file__).parent.parent
    fixer = CaseSensitivityFixer(project_root)
    
    print("=" * 60)
    print("Correção de Case Sensitivity - Projeto Batrip")
    print("=" * 60)
    
    fixer.build_file_map()
    fixer.fix_all()
    
    if fixer.fixes_applied:
        print("\nArquivos modificados:")
        for file in fixer.fixes_applied:
            print(f"  - {file}")
    else:
        print("\nNenhuma correção necessária!")
    
    print("\nConcluído!")

if __name__ == '__main__':
    main()

