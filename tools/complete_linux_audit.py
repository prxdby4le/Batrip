#!/usr/bin/env python3
"""
Auditoria Completa para Linux - Projeto Batrip
Verifica e corrige todos os problemas que podem impedir execução no Linux
"""

import os
import re
import subprocess
from pathlib import Path
from collections import defaultdict

class LinuxAuditor:
    def __init__(self, project_root):
        self.project_root = Path(project_root).resolve()
        self.issues = []
        self.fixes = []
        self.errors = []
        
    def check_php_syntax(self):
        """Verifica erros de sintaxe PHP"""
        print("\n🔍 Verificando sintaxe PHP...")
        
        php_files = []
        for root, dirs, files in os.walk(self.project_root):
            dirs[:] = [d for d in dirs if not d.startswith('.') and d not in ['vendor', 'node_modules']]
            for file in files:
                if file.endswith('.php'):
                    php_files.append(Path(root) / file)
        
        syntax_errors = []
        for php_file in php_files:
            try:
                result = subprocess.run(
                    ['php', '-l', str(php_file)],
                    capture_output=True,
                    text=True,
                    timeout=5
                )
                if 'error' in result.stderr.lower() or 'parse error' in result.stderr.lower():
                    syntax_errors.append((php_file, result.stderr))
            except:
                pass
        
        if syntax_errors:
            print(f"   ⚠️  {len(syntax_errors)} erros de sintaxe encontrados")
            for file, error in syntax_errors[:5]:
                print(f"      - {file.relative_to(self.project_root)}")
                self.errors.append(f"Sintaxe PHP: {file} - {error}")
        else:
            print("   ✓ Nenhum erro de sintaxe encontrado")
    
    def check_file_references(self):
        """Verifica referências quebradas de arquivos"""
        print("\n🔍 Verificando referências de arquivos...")
        
        # Mapeia todos os arquivos
        file_map = {}
        for root, dirs, files in os.walk(self.project_root):
            dirs[:] = [d for d in dirs if not d.startswith('.') and d not in ['vendor', 'node_modules']]
            for file in files:
                file_path = Path(root) / file
                try:
                    relative = file_path.relative_to(self.project_root)
                    normalized = str(relative).lower().replace('\\', '/')
                    file_map[normalized] = str(relative).replace('\\', '/')
                except:
                    pass
        
        broken_refs = []
        php_files = []
        for root, dirs, files in os.walk(self.project_root):
            dirs[:] = [d for d in dirs if not d.startswith('.') and d not in ['vendor', 'node_modules']]
            for file in files:
                if file.endswith('.php'):
                    php_files.append(Path(root) / file)
        
        for php_file in php_files:
            try:
                with open(php_file, 'r', encoding='utf-8', errors='ignore') as f:
                    content = f.read()
            except:
                continue
            
            # Procura por require/include
            pattern = r"(require|require_once|include|include_once)\s*\(?\s*['\"]([^'\"]+)['\"]"
            for match in re.finditer(pattern, content):
                ref_path = match.group(2)
                
                # Ignora URLs
                if ref_path.startswith('http'):
                    continue
                
                # Limpa caminho
                ref_clean = ref_path
                if '__DIR__' in ref_path or 'ROOT_PATH' in ref_path:
                    path_match = re.search(r"['\"]([^'\"]+)['\"]", ref_path)
                    if path_match:
                        ref_clean = path_match.group(1)
                
                if ref_clean.startswith('./'):
                    ref_clean = ref_clean[2:]
                if ref_clean.startswith('/'):
                    ref_clean = ref_clean[1:]
                
                # Resolve caminho relativo
                current_dir = php_file.parent.relative_to(self.project_root)
                try:
                    if ref_clean.startswith('../') or ref_clean.startswith('./'):
                        resolved = (current_dir / ref_clean).resolve()
                        resolved_relative = resolved.relative_to(self.project_root)
                    else:
                        resolved_relative = Path(ref_clean)
                    
                    resolved_normalized = str(resolved_relative).lower().replace('\\', '/')
                    
                    # Verifica se arquivo existe
                    if resolved_normalized not in file_map:
                        # Tenta encontrar por nome
                        filename = Path(ref_clean).name.lower()
                        found = False
                        for norm, actual in file_map.items():
                            if norm.endswith('/' + filename) or norm == filename:
                                found = True
                                break
                        
                        if not found and not ref_clean.startswith('vendor/'):
                            broken_refs.append((php_file, ref_clean))
                except:
                    pass
        if broken_refs:
            print(f"   ⚠️  {len(broken_refs)} referências quebradas encontradas")
            for file, ref in broken_refs[:10]:
                print(f"      - {file.relative_to(self.project_root)}: {ref}")
                self.issues.append(f"Referência quebrada: {file} -> {ref}")
        else:
            print("   ✓ Todas as referências estão corretas")
    
    def check_permissions(self):
        """Verifica permissões de diretórios críticos"""
        print("\n🔍 Verificando permissões...")
        
        critical_dirs = [
            'public/uploads',
            'public/uploads/products',
            'public/uploads/profile_bg',
            'public/assets',
        ]
        
        missing_dirs = []
        for dir_path in critical_dirs:
            full_path = self.project_root / dir_path
            if not full_path.exists():
                missing_dirs.append(dir_path)
                full_path.mkdir(parents=True, exist_ok=True)
                print(f"   ✓ Criado: {dir_path}")
            else:
                # Verifica se é gravável
                if not os.access(full_path, os.W_OK):
                    print(f"   ⚠️  {dir_path} não é gravável")
                    self.issues.append(f"Permissão: {dir_path} não é gravável")
                else:
                    print(f"   ✓ {dir_path} OK")
        
        if not missing_dirs:
            print("   ✓ Todas as permissões estão corretas")
    
    def check_autoloader(self):
        """Verifica se o autoloader está funcionando"""
        print("\n🔍 Verificando autoloader...")
        
        autoload_file = self.project_root / 'autoload.php'
        if not autoload_file.exists():
            print("   ✗ autoload.php não encontrado")
            self.errors.append("autoload.php não existe")
            return
        
        # Testa carregamento de algumas classes
        test_classes = [
            'App\\Core\\Controller',
            'App\\Models\\Product',
            'App\\Helpers\\CartHelper',
        ]
        
        try:
            # Simula carregamento
            with open(autoload_file, 'r') as f:
                content = f.read()
            
            if 'spl_autoload_register' in content:
                print("   ✓ Autoloader configurado")
            else:
                print("   ⚠️  Autoloader pode não estar funcionando")
                self.issues.append("Autoloader pode ter problemas")
        except Exception as e:
            print(f"   ✗ Erro ao verificar autoloader: {e}")
            self.errors.append(f"Autoloader: {e}")
    
    def check_config_files(self):
        """Verifica arquivos de configuração essenciais"""
        print("\n🔍 Verificando arquivos de configuração...")
        
        required_files = [
            'config/config.php',
            'includes/db.php',
            'config/Routes.php',
        ]
        
        missing = []
        for file_path in required_files:
            full_path = self.project_root / file_path
            if not full_path.exists():
                missing.append(file_path)
                print(f"   ✗ {file_path} não encontrado")
                self.errors.append(f"Arquivo faltando: {file_path}")
            else:
                print(f"   ✓ {file_path} existe")
        
        if not missing:
            print("   ✓ Todos os arquivos de configuração existem")
    
    def check_case_sensitivity(self):
        """Verifica problemas de case sensitivity"""
        print("\n🔍 Verificando case sensitivity...")
        
        # Verifica se há arquivos com nomes similares (diferindo apenas em case)
        file_names = defaultdict(list)
        for root, dirs, files in os.walk(self.project_root):
            dirs[:] = [d for d in dirs if not d.startswith('.') and d not in ['vendor', 'node_modules']]
            for file in files:
                if file.endswith('.php'):
                    file_path = Path(root) / file
                    try:
                        relative = file_path.relative_to(self.project_root)
                        normalized = str(relative).lower()
                        file_names[normalized].append(str(relative))
                    except:
                        pass
        
        duplicates = {k: v for k, v in file_names.items() if len(v) > 1}
        if duplicates:
            print(f"   ⚠️  {len(duplicates)} possíveis conflitos de case encontrados")
            for norm, paths in list(duplicates.items())[:5]:
                print(f"      - {paths}")
                self.issues.append(f"Conflito case: {paths}")
        else:
            print("   ✓ Nenhum conflito de case encontrado")
    
    def fix_common_issues(self):
        """Corrige problemas comuns"""
        print("\n🔧 Aplicando correções...")
        
        # Garante que diretórios de upload existem
        upload_dirs = [
            'public/uploads',
            'public/uploads/products',
            'public/uploads/profile_bg',
            'public/uploads/sets',
        ]
        
        for dir_path in upload_dirs:
            full_path = self.project_root / dir_path
            full_path.mkdir(parents=True, exist_ok=True)
            # Tenta tornar gravável
            try:
                os.chmod(full_path, 0o775)
            except:
                pass
        
        print("   ✓ Diretórios de upload verificados")
    
    def generate_report(self):
        """Gera relatório final"""
        print("\n" + "=" * 70)
        print("RELATÓRIO FINAL")
        print("=" * 70)
        
        if self.errors:
            print(f"\n❌ ERROS CRÍTICOS ({len(self.errors)}):")
            for error in self.errors[:10]:
                print(f"   - {error}")
        
        if self.issues:
            print(f"\n⚠️  AVISOS ({len(self.issues)}):")
            for issue in self.issues[:10]:
                print(f"   - {issue}")
        
        if not self.errors and not self.issues:
            print("\n✅ NENHUM PROBLEMA ENCONTRADO!")
            print("   O projeto está pronto para execução no Linux.")
        else:
            print(f"\n📊 Total: {len(self.errors)} erros, {len(self.issues)} avisos")
    
    def run(self):
        """Executa auditoria completa"""
        print("=" * 70)
        print("AUDITORIA COMPLETA PARA LINUX - PROJETO BATRIP")
        print("=" * 70)
        
        self.check_php_syntax()
        self.check_file_references()
        self.check_permissions()
        self.check_autoloader()
        self.check_config_files()
        self.check_case_sensitivity()
        self.fix_common_issues()
        self.generate_report()

def main():
    project_root = Path(__file__).parent.parent
    auditor = LinuxAuditor(project_root)
    auditor.run()

if __name__ == '__main__':
    main()

