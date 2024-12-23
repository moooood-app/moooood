import { Construct } from "constructs";
import { TerraformStack } from "cdktf";
import { RdsCluster } from "@cdktf/provider-aws/lib/rds-cluster";
import { RdsClusterInstance } from "@cdktf/provider-aws/lib/rds-cluster-instance";
import { DataAwsSecretsmanagerSecretVersion } from "@cdktf/provider-aws/lib/data-aws-secretsmanager-secret-version";
import { SecurityGroup } from "@cdktf/provider-aws/lib/security-group";
import { DbSubnetGroup } from "@cdktf/provider-aws/lib/db-subnet-group";
import { KmsAlias } from "@cdktf/provider-aws/lib/kms-alias";
import { SecretsmanagerSecret } from "@cdktf/provider-aws/lib/secretsmanager-secret";
import { Subnet } from "@cdktf/provider-aws/lib/subnet";
import { AwsProvider } from "@cdktf/provider-aws/lib/provider";

interface DatabaseStackProps {
    region: string;
    privateSubnets: Subnet[];
    databaseSecurityGroup: SecurityGroup;
    rdsMasterSecret: SecretsmanagerSecret;
    kmsAlias: KmsAlias
}

export class DatabaseStack extends TerraformStack {
    constructor(scope: Construct, id: string, {
        region,
        privateSubnets,
        databaseSecurityGroup,
        rdsMasterSecret,
        kmsAlias,
    }: DatabaseStackProps) {
        super(scope, id);
        
        new AwsProvider(this, "AWS", {
            region,
        });

        // Fetch the latest secret version
        const secretVersion = new DataAwsSecretsmanagerSecretVersion(this, "MasterSecretVersion", {
            secretId: rdsMasterSecret.id,
        });

        // Decode the secret for username and password
        const masterUsername = `\${jsondecode(${secretVersion.secretString})["username"]}`;
        const masterPassword = `\${jsondecode(${secretVersion.secretString})["password"]}`;

        // Define a DB Subnet Group
        const dbSubnetGroup = new DbSubnetGroup(this, "AuroraDBSubnetGroup", {
            name: "aurora-db-subnet-group",
            subnetIds: privateSubnets.map((subnet) => subnet.id),
            tags: { Name: "moooood-aurora-db-subnet-group" },
        });

        // Create the Aurora Cluster
        const auroraCluster = new RdsCluster(this, "AuroraPostgresCluster", {
            clusterIdentifier: "aurora-postgres-cluster",
            engine: "aurora-postgresql",
            engineMode: "provisioned",
            engineVersion: "16.2",
            masterUsername,
            masterPassword,
            kmsKeyId: kmsAlias.name,
            vpcSecurityGroupIds: [databaseSecurityGroup.id],
            dbSubnetGroupName: dbSubnetGroup.name,
            skipFinalSnapshot: true,
            deletionProtection: true,
            tags: { Name: "moooood-aurora-cluster" },
        });

        // Create an Aurora Instance
        new RdsClusterInstance(this, "AuroraPostgresInstance", {
            identifier: "moooood-postgres-instance-1",
            clusterIdentifier: auroraCluster.id,
            instanceClass: "db.t3.micro", 
            engine: auroraCluster.engine,
            engineVersion: auroraCluster.engineVersion,
            publiclyAccessible: false, 
            tags: { Name: "moooood-aurora-instance" },
        });
    }
}
