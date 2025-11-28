#!/usr/bin/env python3
"""
Auditoria Completa e Correção de Case Sensitivity
- Verifica e corrige nomes de arquivos de classes (PSR-4)
- Corrige includes/requires para bater com arquivos reais
- Verifica e corrige estrutura de pastas para namespaces
"""

import os
import re
from pathlib import Path
from collections import defaultdict
import shutil

class CompleteCaseAuditor:
    def __init__(self, project_root):
        self.project_root = Path(project_root).resolve()
        self.file_map = {}  # normalized -> actual
        self.class_map = {}  # class_name -> file_path
        self.namespace_map = {}  # namespace -> expected_folder
        self.renames = []  # (old_path, new_path)
        self.fixes = []  # (file, line, old, new)
        self.errors = []
        
    def build_file_map(self):
        """Mapeia todos os arquivos reais do projeto"""
        print("📁 Mapeando estrutura de arquivos...")
        
        for root, dirs, files in os.walk(self.project_root):
            dirs[:] = [d for d in dirs if not d.startswith('.') and d not in ['vendor', 'node_modules']]
            
            root_path = Path(root)
            for file in files:
                if file.endswith('.php'):
                    file_path = root_path / file
                    try:
                        relative_path = file_path.relative_to(self.project_root)
                        normalized = str(relative_path).lower().replace('\\', '/')
                        actual = str(relative_path).replace('\\', '/')
                        
                        if normalized not in self.file_map:
                            self.file_map[normalized] = actual
                    except ValueError:
                        pass
        
        print(f"   ✓ {len(self.file_map)} arquivos mapeados")
    
    def extract_classes(self, file_path):
        """Extrai classes definidas em um arquivo PHP"""
        try:
            with open(file_path, 'r', encoding='utf-8', errors='ignore') as f:
                content = f.read()
        except:
            return []
        
        classes = []
        # Padrão para class NomeClasse ou class NomeClasse extends
        pattern = r'^\s*(?:abstract\s+|final\s+)?class\s+(\w+)\s*(?:extends|implements|\{|$)'
        
        for match in re.finditer(pattern, content, re.MULTILINE):
            class_name = match.group(1)
            classes.append(class_name)
        
        return classes
    
    def extract_namespace(self, file_path):
        """Extrai namespace de um arquivo PHP"""
        try:
            with open(file_path, 'r', encoding='utf-8', errors='ignore') as f:
                for line in f:
                    match = re.match(r'^\s*namespace\s+([^;]+);', line)
                    if match:
                        return match.group(1).strip()
        except:
            pass
        return None
    
    def audit_classes(self):
        """Audita e corrige nomes de arquivos de classes (PSR-4)"""
        print("\n🔍 Auditing classes (PSR-4)...")
        
        php_files = []
        for root, dirs, files in os.walk(self.project_root):
            dirs[:] = [d for d in dirs if not d.startswith('.') and d not in ['vendor', 'node_modules']]
            for file in files:
                if file.endswith('.php'):
                    php_files.append(Path(root) / file)
        
        classes_to_rename = []
        
        for php_file in php_files:
            classes = self.extract_classes(php_file)
            if not classes:
                continue
            
            class_name = classes[0]  # Primeira classe encontrada
            expected_filename = class_name + '.php'
            actual_filename = php_file.name
            
            # Verifica se precisa renomear
            if actual_filename != expected_filename and actual_filename.lower() == expected_filename.lower():
                classes_to_rename.append((php_file, expected_filename, class_name))
        
        # Renomeia arquivos
        for php_file, expected_filename, class_name in classes_to_rename:
            new_path = php_file.parent / expected_filename
            
            # Verifica se já existe arquivo com o nome correto
            if new_path.exists() and new_path != php_file:
                print(f"   ⚠️  Arquivo {new_path.name} já existe, pulando {php_file.name}")
                continue
            
            try:
                php_file.rename(new_path)
                relative_old = php_file.relative_to(self.project_root)
                relative_new = new_path.relative_to(self.project_root)
                self.renames.append((str(relative_old), str(relative_new)))
                print(f"   ✓ Renomeado: {relative_old} → {relative_new} (classe: {class_name})")
                
                # Atualiza file_map
                old_normalized = str(relative_old).lower().replace('\\', '/')
                new_normalized = str(relative_new).lower().replace('\\', '/')
                if old_normalized in self.file_map:
                    del self.file_map[old_normalized]
                self.file_map[new_normalized] = str(relative_new).replace('\\', '/')
            except Exception as e:
                print(f"   ✗ Erro ao renomear {php_file}: {e}")
        
        print(f"   ✓ {len(classes_to_rename)} arquivos de classe renomeados")
    
    def audit_namespace_folders(self):
        """Audita e corrige estrutura de pastas para corresponder aos namespaces"""
        print("\n🔍 Auditing namespace folders...")
        
        php_files = []
        for root, dirs, files in os.walk(self.project_root):
            dirs[:] = [d for d in dirs if not d.startswith('.') and d not in ['vendor', 'node_modules']]
            for file in files:
                if file.endswith('.php'):
                    php_files.append(Path(root) / file)
        
        folders_to_rename = []
        
        for php_file in php_files:
            namespace = self.extract_namespace(php_file)
            if not namespace or not namespace.startswith('App\\'):
                continue
            
            # Converte namespace para estrutura de pastas esperada
            namespace_parts = namespace.replace('App\\', '').split('\\')
            if not namespace_parts or namespace_parts == ['']:
                continue
            
            # Pasta esperada (PascalCase)
            expected_folder = namespace_parts[0]
            actual_folder = php_file.parent.name
            
            # Verifica se precisa renomear a pasta
            if actual_folder != expected_folder and actual_folder.lower() == expected_folder.lower():
                # Verifica se está na estrutura app/
                if 'app' in str(php_file.parent).lower():
                    folders_to_rename.append((php_file.parent, expected_folder))
        
        # Remove duplicatas
        folders_to_rename = list(set(folders_to_rename))
        
        # Renomeia pastas
        for folder_path, expected_name in folders_to_rename:
            new_path = folder_path.parent / expected_name
            
            # Verifica se já existe
            if new_path.exists() and new_path != folder_path:
                print(f"   ⚠️  Pasta {expected_name} já existe, pulando {folder_path.name}")
                continue
            
            try:
                folder_path.rename(new_path)
                print(f"   ✓ Pasta renomeada: {folder_path.name} → {expected_name}")
                
                # Atualiza todos os arquivos dentro
                for root, dirs, files in os.walk(new_path):
                    for file in files:
                        if file.endswith('.php'):
                            file_path = Path(root) / file
                            try:
                                relative = file_path.relative_to(self.project_root)
                                normalized = str(relative).lower().replace('\\', '/')
                                actual = str(relative).replace('\\', '/')
                                self.file_map[normalized] = actual
                            except:
                                pass
            except Exception as e:
                print(f"   ✗ Erro ao renomear pasta {folder_path}: {e}")
        
        print(f"   ✓ {len(folders_to_rename)} pastas renomeadas")
    
    def find_real_file(self, ref_path, current_file):
        """Encontra o arquivo real correspondente"""
        ref_clean = ref_path
        if '__DIR__' in ref_path or 'ROOT_PATH' in ref_path:
            match = re.search(r"['\"]([^'\"]+)['\"]", ref_path)
            if match:
                ref_clean = match.group(1)
        
        if ref_clean.startswith('./'):
            ref_clean = ref_clean[2:]
        if ref_clean.startswith('/'):
            ref_clean = ref_clean[1:]
        
        ref_normalized = ref_clean.lower().replace('\\', '/')
        
        if ref_normalized in self.file_map:
            return self.file_map[ref_normalized]
        
        # Tenta resolver relativo
        try:
            current_dir = Path(current_file).parent.relative_to(self.project_root)
            if ref_clean.startswith('../') or ref_clean.startswith('./'):
                resolved = (current_dir / ref_clean).resolve()
                try:
                    resolved_relative = resolved.relative_to(self.project_root)
                    resolved_normalized = str(resolved_relative).lower().replace('\\', '/')
                    if resolved_normalized in self.file_map:
                        return self.file_map[resolved_normalized]
                except ValueError:
                    pass
        except:
            pass
        
        # Busca por nome de arquivo
        filename = Path(ref_clean).name.lower()
        matches = []
        for normalized, actual in self.file_map.items():
            if normalized.endswith('/' + filename) or normalized == filename:
                matches.append(actual)
        
        if len(matches) == 1:
            return matches[0]
        elif len(matches) > 1:
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
    
    def fix_file_references(self):
        """Corrige todas as referências de arquivos"""
        print("\n🔧 Corrigindo referências de arquivos...")
        
        php_files = []
        for root, dirs, files in os.walk(self.project_root):
            dirs[:] = [d for d in dirs if not d.startswith('.') and d not in ['vendor', 'node_modules']]
            for file in files:
                if file.endswith('.php'):
                    php_files.append(Path(root) / file)
        
        fixed_count = 0
        
        for php_file in php_files:
            try:
                with open(php_file, 'r', encoding='utf-8', errors='ignore') as f:
                    content = f.read()
            except:
                continue
            
            original_content = content
            relative_file = php_file.relative_to(self.project_root)
            
            # Padrões
            patterns = [
                (r"(require|require_once|include|include_once)\s*\(\s*([^)]*['\"]([^'\"]+)['\"][^)]*)\)", 3),
                (r"(require|require_once|include|include_once)\s*\(\s*['\"]([^'\"]+)['\"]", 2),
                (r"(require|require_once|include|include_once)\s+['\"]([^'\"]+)['\"]", 2),
            ]
            
            modified = False
            
            for pattern, group_idx in patterns:
                def replace_match(match):
                    nonlocal modified
                    full_match = match.group(0)
                    ref_path = match.group(group_idx)
                    
                    if ref_path.startswith('http://') or ref_path.startswith('https://'):
                        return full_match
                    
                    correct_path = self.find_real_file(ref_path, php_file)
                    
                    if correct_path and correct_path != ref_path:
                        current_dir = Path(php_file).parent.relative_to(self.project_root)
                        try:
                            rel_path = os.path.relpath(
                                self.project_root / correct_path,
                                self.project_root / current_dir
                            ).replace('\\', '/')
                            
                            if not rel_path.startswith('.'):
                                rel_path = './' + rel_path
                            
                            if '__DIR__' in full_match or 'ROOT_PATH' in full_match:
                                if 'ROOT_PATH' in full_match:
                                    new_path = f"ROOT_PATH . '/{correct_path}'"
                                else:
                                    new_path = f"__DIR__ . '/{rel_path}'"
                            else:
                                new_path = rel_path
                            
                            modified = True
                            self.fixes.append((str(relative_file), ref_path, new_path))
                            return full_match.replace(ref_path, new_path)
                        except:
                            if 'ROOT_PATH' in full_match:
                                new_path = f"ROOT_PATH . '/{correct_path}'"
                                modified = True
                                self.fixes.append((str(relative_file), ref_path, new_path))
                                return full_match.replace(ref_path, new_path)
                    
                    return full_match
                
                content = re.sub(pattern, replace_match, content)
            
            if modified:
                try:
                    with open(php_file, 'w', encoding='utf-8') as f:
                        f.write(content)
                    fixed_count += 1
                    print(f"   ✓ {relative_file}")
                except Exception as e:
                    print(f"   ✗ Erro em {relative_file}: {e}")
        
        print(f"   ✓ {fixed_count} arquivos corrigidos")
    
    def run(self):
        """Executa auditoria completa"""
        print("=" * 70)
        print("AUDITORIA COMPLETA DE CASE SENSITIVITY - PROJETO BATRIP")
        print("=" * 70)
        
        self.build_file_map()
        self.audit_classes()
        self.audit_namespace_folders()
        self.build_file_map()  # Re-mapeia após renomeações
        self.fix_file_references()
        
        print("\n" + "=" * 70)
        print("RESUMO")
        print("=" * 70)
        print(f"Arquivos renomeados: {len(self.renames)}")
        print(f"Referências corrigidas: {len(self.fixes)}")
        
        if self.renames:
            print("\nArquivos Renomeados:")
            for old, new in self.renames:
                print(f"  {old} → {new}")
        
        print("\n✓ Auditoria completa concluída!")

def main():
    project_root = Path(__file__).parent.parent
    auditor = CompleteCaseAuditor(project_root)
    auditor.run()

if __name__ == '__main__':
    main()

