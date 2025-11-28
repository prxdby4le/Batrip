#!/usr/bin/env python3
"""
Script completo para corrigir TODAS as inconsistências de case sensitivity.
Analisa arquivos reais e corrige todas as referências no código.
"""

import os
import re
from pathlib import Path
from collections import defaultdict

class CompleteCaseSensitivityFixer:
    def __init__(self, project_root):
        self.project_root = Path(project_root).resolve()
        self.file_map = {}  # Mapeia caminhos normalizados para caminhos reais
        self.fixes_applied = []
        self.errors_found = []
        
    def build_file_map(self):
        """Constrói um mapa completo de todos os arquivos reais do projeto"""
        print("Mapeando estrutura completa de arquivos...")
        
        for root, dirs, files in os.walk(self.project_root):
            # Ignora diretórios ocultos, git e .old
            dirs[:] = [d for d in dirs if not d.startswith('.') and d != 'vendor' and d != 'node_modules']
            
            root_path = Path(root)
            for file in files:
                if file.endswith(('.php', '.js', '.css', '.html', '.json')):
                    file_path = root_path / file
                    try:
                        relative_path = file_path.relative_to(self.project_root)
                        
                        # Cria chave normalizada (lowercase) para busca case-insensitive
                        normalized = str(relative_path).lower().replace('\\', '/')
                        actual = str(relative_path).replace('\\', '/')
                        
                        # Se já existe uma entrada, mantém a primeira (mais comum)
                        if normalized not in self.file_map:
                            self.file_map[normalized] = actual
                    except ValueError:
                        # Arquivo fora do projeto root
                        pass
        
        print(f"Mapeados {len(self.file_map)} arquivos")
    
    def find_real_file(self, ref_path, current_file):
        """
        Encontra o arquivo real correspondente a uma referência.
        Retorna o caminho correto ou None se não encontrar.
        """
        # Remove variáveis como __DIR__ e ROOT_PATH
        ref_path_clean = ref_path
        if '__DIR__' in ref_path or 'ROOT_PATH' in ref_path or 'dirname' in ref_path:
            # Extrai apenas a parte do caminho após a concatenação
            match = re.search(r"['\"]([^'\"]+)['\"]", ref_path)
            if match:
                ref_path_clean = match.group(1)
        
        # Remove ./ do início
        if ref_path_clean.startswith('./'):
            ref_path_clean = ref_path_clean[2:]
        
        # Remove / do início se existir
        if ref_path_clean.startswith('/'):
            ref_path_clean = ref_path_clean[1:]
        
        # Normaliza
        ref_normalized = ref_path_clean.lower().replace('\\', '/')
        
        # Tenta encontrar correspondência exata
        if ref_normalized in self.file_map:
            return self.file_map[ref_normalized]
        
        # Tenta resolver caminho relativo
        try:
            current_dir = Path(current_file).parent.relative_to(self.project_root)
            if ref_path_clean.startswith('../') or ref_path_clean.startswith('./'):
                resolved = (current_dir / ref_path_clean).resolve()
                try:
                    resolved_relative = resolved.relative_to(self.project_root)
                    resolved_normalized = str(resolved_relative).lower().replace('\\', '/')
                    if resolved_normalized in self.file_map:
                        return self.file_map[resolved_normalized]
                except ValueError:
                    pass
        except:
            pass
        
        # Busca por nome de arquivo apenas
        filename = Path(ref_path_clean).name.lower()
        matches = []
        for normalized, actual in self.file_map.items():
            if normalized.endswith('/' + filename) or normalized == filename:
                matches.append(actual)
        
        if len(matches) == 1:
            return matches[0]
        elif len(matches) > 1:
            # Escolhe a correspondência mais próxima
            current_parts = str(Path(current_file).parent.relative_to(self.project_root)).lower().split('/')
            best_match = matches[0]
            best_score = 0
            for match in matches:
                match_parts = match.lower().split('/')
                score = sum(1 for a, b in zip(current_parts, match_parts) if a == b)
                if score > best_score:
                    best_score = score
                    best_match = match
            return best_match
        
        return None
    
    def fix_file(self, file_path):
        """Corrige todas as referências em um arquivo"""
        try:
            with open(file_path, 'r', encoding='utf-8', errors='ignore') as f:
                content = f.read()
        except Exception as e:
            print(f"Erro ao ler {file_path}: {e}")
            return False
        
        original_content = content
        relative_file = Path(file_path).relative_to(self.project_root)
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
                
                # Ignora URLs e caminhos absolutos que começam com http
                if ref_path.startswith('http://') or ref_path.startswith('https://'):
                    return full_match
                
                # Encontra o arquivo real
                correct_path = self.find_real_file(ref_path, file_path)
                
                if correct_path and correct_path != ref_path:
                    # Verifica se o caminho precisa ser ajustado para relativo
                    current_dir = Path(file_path).parent.relative_to(self.project_root)
                    correct_path_obj = Path(correct_path)
                    
                    # Calcula caminho relativo
                    try:
                        rel_path = os.path.relpath(
                            self.project_root / correct_path,
                            self.project_root / current_dir
                        ).replace('\\', '/')
                        
                        # Se o original tinha __DIR__ ou similar, mantém o padrão
                        if '__DIR__' in full_match or 'ROOT_PATH' in full_match:
                            # Mantém a estrutura original mas com caminho correto
                            if 'ROOT_PATH' in full_match:
                                new_path = f"ROOT_PATH . '/{correct_path}'"
                            elif '__DIR__' in full_match:
                                # Calcula quantos ../ são necessários
                                depth = len(current_dir.parts)
                                if depth == 0:
                                    new_path = f"__DIR__ . '/{correct_path}'"
                                else:
                                    # Tenta usar caminho relativo
                                    rel_path = os.path.relpath(
                                        self.project_root / correct_path,
                                        self.project_root / current_dir
                                    ).replace('\\', '/')
                                    if not rel_path.startswith('.'):
                                        rel_path = './' + rel_path
                                    new_path = f"__DIR__ . '/{rel_path}'"
                            else:
                                new_path = rel_path
                        else:
                            new_path = rel_path
                        
                        modified = True
                        # Substitui mantendo aspas originais
                        quote = "'" if "'" in full_match else '"'
                        return full_match.replace(ref_path, new_path)
                    except:
                        # Se não conseguir calcular relativo, usa caminho absoluto do projeto
                        if 'ROOT_PATH' in full_match:
                            new_path = f"ROOT_PATH . '/{correct_path}'"
                            modified = True
                            return full_match.replace(ref_path, new_path)
                
                # Se não encontrou arquivo, registra erro
                if not correct_path and not ref_path.startswith('http'):
                    self.errors_found.append({
                        'file': str(relative_file),
                        'line': content[:match.start()].count('\n') + 1,
                        'reference': ref_path
                    })
                
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
            dirs[:] = [d for d in dirs if not d.startswith('.') and d != 'vendor' and d != 'node_modules']
            for file in files:
                if file.endswith('.php'):
                    php_files.append(Path(root) / file)
        
        fixed_count = 0
        for php_file in php_files:
            if self.fix_file(php_file):
                fixed_count += 1
                print(f"  ✓ Corrigido: {php_file.relative_to(self.project_root)}")
        
        print(f"\nTotal de arquivos corrigidos: {fixed_count}")
        return fixed_count

def main():
    project_root = Path(__file__).parent.parent
    fixer = CompleteCaseSensitivityFixer(project_root)
    
    print("=" * 70)
    print("Correção Completa de Case Sensitivity - Projeto Batrip")
    print("=" * 70)
    
    fixer.build_file_map()
    fixer.fix_all()
    
    if fixer.fixes_applied:
        print("\n" + "=" * 70)
        print("Arquivos Modificados:")
        print("=" * 70)
        for file in fixer.fixes_applied:
            print(f"  - {file}")
    
    if fixer.errors_found:
        print("\n" + "=" * 70)
        print("AVISOS: Referências não encontradas (verifique manualmente):")
        print("=" * 70)
        for error in fixer.errors_found[:20]:  # Limita a 20
            print(f"  - {error['file']}:{error['line']} → {error['reference']}")
        if len(fixer.errors_found) > 20:
            print(f"  ... e mais {len(fixer.errors_found) - 20} referências")
    
    print("\n" + "=" * 70)
    print("Concluído!")
    print("=" * 70)

if __name__ == '__main__':
    main()

