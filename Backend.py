#!/bin/python3
import time
import sys,os,re,numpy,requests
import subprocess as sp
from Bio import SeqIO
from Bio import Entrez
s=[]
#Get our info from the php
user_id = sys.argv[1] if len(sys.argv) > 1 else "None"
species= sys.argv[2] if len(sys.argv) > 1 else "None"
protein = sys.argv[3] if len(sys.argv) > 1 else "None"
print(sys.argv)


if sys.argv[2] == '' or sys.argv[3] == '' or "None" in sys.argv:
    print("No proteins found.")
    exit()
protein_ids=[]
with open(f"{species}_{protein}_{user_id}pepresults.txt","w") as pepres:
    pepres.write("ID\tMolecularWeight\tResidueCount\tResidueWeight\tCharge\tIsoelectricPoint\tExtinctionReduced\tExtinctionBridges\tReducedMgMl\tBridgeMgMl\tProbability_pos_neg\n")
with open(f"{species}_{protein}_{user_id}results.fasta","w") as fasta:
    fasta.write("")
with open(f"{species}_{protein}_{user_id}resultsprosite.tsv","w") as proresults:
    proresults.write("SeqName\tStart\tEnd\tScore\tStrand\tMotif\n")

#Run the esearch function then efetch if we get results.
print(f"{user_id}")
Entrez.email="s2761220@ed.ac.uk"
time.sleep(3)
search_handle = Entrez.esearch(db="protein", term=f"{species}[Organism] AND {protein}[Protein]", retmax=100)
search_results = Entrez.read(search_handle)
search_handle.close()
protein_ids = search_results['IdList']
if protein_ids:
    # Fetch the sequences for the found protein IDs
    fetch_handle = Entrez.efetch(db="protein", id=protein_ids, rettype="fasta", retmode="text")
    fasta_data = fetch_handle.read()
    fetch_handle.close()
    file_name = f"{species}_{protein}_{user_id}results.fasta"
    with open(file_name, "w") as fasta_file:
        fasta_file.write(fasta_data)
    
    print(str(len(protein_ids))+" proteins found.")  
    fetch_handle = Entrez.efetch(db="protein", id=protein_ids, rettype="xml")
    tsv_data = Entrez.read(fetch_handle)
    fetch_handle.close()
    tsv_lines = ["SeqName\tOrganism\tDefinition\tLength"]
    for entry in tsv_data:
        accession = entry.get("GBSeq_accession-version","N/A")
        organism = entry.get("GBSeq_organism","N/A")
        definition = entry.get("GBSeq_definition","N/A")
        length = entry.get("GBSeq_length","N/A")
        tsv_lines.append(f"{accession}\t{organism}\t{definition}\t{length}")
    file_name = f"{species}_{protein}_{user_id}results.tsv"
    with open(file_name, "w") as tsv_file:
        tsv_file.write("\n".join(tsv_lines))

    
#Run some analysis
    sp.call(f"plotcon -sequences {species}_{protein}_{user_id}results.fasta -winsize 10 -graph png",shell=True)
    sp.call(f"cp plotcon.1.png {species}_{protein}_{user_id}.plotcon.png",shell=True)
    sp.call(f"pepstats {species}_{protein}_{user_id}results.fasta -outfile {species}_{protein}_{user_id}pepstats.txt",shell=True)
    if len(protein_ids) < 20:
        sp.call(f"prettyplot {user_id}results.fasta -graph pdf",shell=True)
        sp.call(f"mv prettyplot.pdf {species}_{protein}_{user_id}prettyplot.pdf",shell=True)
    with open(f"{species}_{protein}_{user_id}pepstats.txt","r") as pep:
        with open(f"{species}_{protein}_{user_id}pepresults.txt","a") as pepres:
            seqid=molweight=resi=resweight=charge=ipoint=reduced=bridge=reducedex=bridgeex=expression_prob=None
            for lines in pep:
                lines=lines.strip()     
                if "PEPSTATS of " in lines:
                    if all(v is not None for v in (seqid, molweight, resi, resweight, charge, ipoint, reduced, bridge,reducedex,bridgeex, expression_prob)):
                        #print(f"{seqid}\t{molweight}\t{resi}\t{resweight}\t{charge}\t{ipoint}\t{reduced}\t{bridge}\t{reducedex}\t{bridgeex}\t{expression_prob}\n")
                        pepres.write(f"{seqid}\t{molweight}\t{resi}\t{resweight}\t{charge}\t{ipoint}\t{reduced}\t{bridge}\t{reducedex}\t{bridgeex}\t{expression_prob}\n")
                    seqid=molweight=resi=resweight=charge=ipoint=reduced=bridge=reducedex=bridgeex=expression_prob=None
                    seqid=re.search(r"PEPSTATS of ([A-Za-z]+.+[^\s]) from",lines).group(1)
                elif re.search(r"Molecular weight\s+=\s+", lines):
                    molweight=re.search(r"Molecular weight\s+=\s+([\d\.\-]+)",lines).group(1) 
                    resi=re.search(r"Residues = ([\d]+)",lines).group(1) 
                elif re.search(r"Charge\s+=\s+", lines):
                    resweight=re.search(r"Average Residue Weight\s+=\s+([\d\.\-]+)",lines).group(1) 
                    charge=re.search(r"Charge\s+=\s+([\d\.\-]+)",lines).group(1)
                elif re.search(r"Isoelectric Point\s+=\s+", lines):
                    ipoint=re.search(r"([\d\.]+)",lines).group(1)
                elif "A280 Molar" in lines:
                    reduced = re.search(r"(\d+)\s+\(reduced\)", lines).group(1)
                    bridge = re.search(r"(\d+)\s+\(cystine",lines).group(1)  
                elif "A280 Extinction" in lines:
                    reducedex = re.search(r"(\d+)\s+\(reduced\)", lines).group(1) 
                    bridgeex = re.search(r"(\d+)\s+\(cystine",lines).group(1)
                elif "Improbability" in lines:
                    expression_prob ="-"+ re.search(r"([\d\.]+)$", lines).group(1) 
                elif "Probability" in lines:
                    expression_prob=re.search(r"([\d\.]+)$",lines).group(1)
            if all(v is not None for v in (seqid, molweight, resi, resweight, charge, ipoint, reduced, bridge,reducedex,bridgeex, expression_prob)):
                #print(f"{seqid}\t{molweight}\t{resi}\t{resweight}\t{charge}\t{ipoint}\t{reduced}\t{bridge}\t{reducedex}\t{bridgeex}\t{expression_prob}\n")
                pepres.write(f"{seqid}\t{molweight}\t{resi}\t{resweight}\t{charge}\t{ipoint}\t{reduced}\t{bridge}\t{reducedex}\t{bridgeex}\t{expression_prob}\n")

    with open(f"{species}_{protein}_{user_id}results.fasta","r") as fasta:
        for record in SeqIO.parse(fasta,"fasta"):
            #Read sequences from FASTA file
            s.append(record)
            with open(f"{user_id}tmp.fasta","w") as tmp:
                SeqIO.write(record,tmp,"fasta")
            sp.call(f"patmatmotifs -sequence {user_id}tmp.fasta -outfile \"{user_id}{record.id}.tsv\" -noprune -auto -rformat excel",shell=True)
            if os.path.exists(f"{user_id}{record.id}.tsv"):
                with open(f"{user_id}{record.id}.tsv","r") as add:
                    motifres=add.readlines()[1:]
                with open(f"{species}_{protein}_{user_id}resultsprosite.tsv","a") as result:
                    result.writelines(motifres)
                os.remove(f"{user_id}{record.id}.tsv")
                
            else:
                print("no file")
                os.remove(f"{user_id}{record.id}.tsv")
    os.remove(f"{user_id}tmp.fasta")
        
    if len(protein_ids)<=100:
        sp.call(f"clustalo -i {species}_{protein}_{user_id}results.fasta -o {species}_{protein}_{user_id}alignment.fasta --force",shell=True)
    else:
        sp.call(f"mafft --quiet --auto {species}_{protein}_{user_id}results.fasta > {species}_{protein}_{user_id}alignment.fasta -v",shell=True)
    
    #Zip up the user's files
    if os.path.exists(f"{user_id}results.zip"):
        sp.call(f"unzip -o {user_id}results.zip -d {user_id}resultstmp",shell=True)
        sp.call(f"cd {user_id}_temp && zip -r ../{user_id}results *{user_id}*", shell=True)
        sp.call(f"rm -rf {user_id}resultstmp",shell=True)
    else:
        sp.call(f"zip {user_id}results.zip *{user_id}*",shell=True)
    
    print("ok")
    exit()
else:
    print("No proteins found.")
    exit()
